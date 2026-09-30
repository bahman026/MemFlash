#!/bin/bash
#
# Container entrypoint. Prepares the application, then hands PID 1 to pm2-runtime,
# which supervises php-fpm, the queue worker and the scheduler.
#
# Everything here is idempotent and non-destructive: it runs on EVERY container
# start, including restarts of a live server, so it must never drop data.
#
# Behaviour is controlled by environment variables, all with safe defaults:
#
#   DB_FRESH_ON_BOOT=false   true drops and reseeds the database. LOCAL DEV ONLY.
#   RUN_MIGRATIONS=true      apply pending migrations
#   SEED_IF_EMPTY=true       seed only when there is no curriculum content yet
#   BUILD_ASSETS=missing     always | missing | never
#   INSTALL_DEPS=true        composer install on boot
#   CACHE_CONFIG=true        artisan optimize (config, routes, views)
#   RUN_WORKERS=true         false serves web only, no queue or scheduler
#   DB_WAIT_TIMEOUT=60       seconds to wait for the database

set -euo pipefail

APP_DIR="/var/www/html"
ARTISAN="php ${APP_DIR}/artisan"

DB_FRESH_ON_BOOT="${DB_FRESH_ON_BOOT:-false}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-true}"
SEED_IF_EMPTY="${SEED_IF_EMPTY:-true}"
BUILD_ASSETS="${BUILD_ASSETS:-missing}"
INSTALL_DEPS="${INSTALL_DEPS:-true}"
CACHE_CONFIG="${CACHE_CONFIG:-true}"
RUN_WORKERS="${RUN_WORKERS:-true}"
DB_WAIT_TIMEOUT="${DB_WAIT_TIMEOUT:-60}"

log() { printf '\033[0;36m==>\033[0m %s\n' "$*"; }
warn() { printf '\033[0;33m/!\\\033[0m %s\n' "$*"; }

cd "${APP_DIR}"

# ---------------------------------------------------------------------------
# Dependencies
# ---------------------------------------------------------------------------

if [ "${INSTALL_DEPS}" = "true" ]; then
    log "Installing PHP dependencies"
    composer install --no-interaction --optimize-autoloader --no-progress
fi

# ---------------------------------------------------------------------------
# Application key
#
# Without it every request touching a cookie or session dies with
# MissingAppKeyException and only /up answers.
# ---------------------------------------------------------------------------

if [ ! -f "${APP_DIR}/.env" ]; then
    warn "No .env file. Copying .env.example -- review the settings."
    cp "${APP_DIR}/.env.example" "${APP_DIR}/.env"
fi

if ! grep -qE '^APP_KEY=.+' "${APP_DIR}/.env"; then
    log "APP_KEY is empty; generating one"
    ${ARTISAN} key:generate --force
fi

# ---------------------------------------------------------------------------
# Wait for the database
#
# depends_on only waits for the container to start, not for Postgres to accept
# connections, so migrating immediately is a race on a cold boot.
# ---------------------------------------------------------------------------

log "Waiting for the database (up to ${DB_WAIT_TIMEOUT}s)"
waited=0
until ${ARTISAN} db:show >/dev/null 2>&1; do
    if [ "${waited}" -ge "${DB_WAIT_TIMEOUT}" ]; then
        warn "Database not reachable after ${DB_WAIT_TIMEOUT}s. Continuing; migrations will report the real error."
        break
    fi
    sleep 2
    waited=$((waited + 2))
done

# ---------------------------------------------------------------------------
# Schema and data
#
# NEVER migrate:fresh unconditionally here. This script runs on every start, so
# that would wipe all user decks, cards and progress on any restart.
# ---------------------------------------------------------------------------

if [ "${DB_FRESH_ON_BOOT}" = "true" ]; then
    warn "DB_FRESH_ON_BOOT=true -- dropping every table and reseeding. All data will be lost."
    ${ARTISAN} migrate:fresh --seed --force
else
    if [ "${RUN_MIGRATIONS}" = "true" ]; then
        log "Applying pending migrations"
        ${ARTISAN} migrate --force
    fi

    if [ "${SEED_IF_EMPTY}" = "true" ]; then
        # Guarded on "is there any curriculum content?". The StaticCard seeders use
        # firstOrNew so they are content-safe, but seeding an already-populated
        # database is pointless work on every boot.
        NEEDS_SEED="$(php -r '
            require "/var/www/html/vendor/autoload.php";
            $app = require "/var/www/html/bootstrap/app.php";
            $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            try { echo \App\Models\StaticDeck::count() > 0 ? "no" : "yes"; }
            catch (\Throwable $e) { echo "yes"; }
        ' 2>/dev/null || echo "yes")"

        if [ "${NEEDS_SEED}" = "yes" ]; then
            log "No curriculum content found; running the initial seed"
            ${ARTISAN} db:seed --force
        else
            log "Curriculum content present; skipping seed"
        fi
    fi
fi

# ---------------------------------------------------------------------------
# Front-end assets
#
# The layout resolves assets through the Vite manifest, so without a build every
# page throws. Default is to build only when the manifest is missing, since a full
# build adds about a minute to boot.
# ---------------------------------------------------------------------------

build_assets() {
    log "Building front-end assets"
    if [ -f "${APP_DIR}/package-lock.json" ]; then
        npm ci --no-audit --no-fund
    else
        npm install --no-audit --no-fund
    fi
    npm run build
}

case "${BUILD_ASSETS}" in
    always)
        build_assets
        ;;
    missing)
        if [ ! -f "${APP_DIR}/public/build/manifest.json" ]; then
            log "No Vite manifest found"
            build_assets
        else
            log "Vite manifest present; skipping asset build"
        fi
        ;;
    never)
        log "Asset build disabled"
        ;;
    *)
        warn "Unknown BUILD_ASSETS value '${BUILD_ASSETS}'; skipping asset build"
        ;;
esac

# A leftover public/hot makes @vite load from a dev server that is not running, so
# every page renders unstyled. It is transient state, safe to remove.
if [ -f "${APP_DIR}/public/hot" ]; then
    warn "Removing stale public/hot (would make @vite target a dev server)"
    rm -f "${APP_DIR}/public/hot"
fi

# ---------------------------------------------------------------------------
# Caches and links
# ---------------------------------------------------------------------------

${ARTISAN} storage:link || true

${ARTISAN} optimize:clear

if [ "${CACHE_CONFIG}" = "true" ]; then
    log "Caching config, routes and views"
    ${ARTISAN} optimize
else
    warn "Config caching disabled (CACHE_CONFIG=false)"
fi

# Everything above ran as root, but php-fpm serves as www-data. On a Linux host
# the root-owned caches and compiled views would then be unwritable by the web
# process. Worse, if a root process (this script, the queue, the scheduler)
# creates laravel.log first, every later request that logs fails with a 500.
# Creating the log here and handing both trees to www-data prevents both.
touch "${APP_DIR}/storage/logs/laravel.log"
chown -R www-data:www-data "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache" \
    || warn "Could not chown storage/ and bootstrap/cache/ to www-data"

# ---------------------------------------------------------------------------
# Hand over to PM2
#
# pm2-runtime, not `pm2 start`: it stays in the foreground as PID 1, forwards
# signals for a clean shutdown, and streams logs to stdout where Docker collects
# them. Daemon mode would exit immediately and leave nothing supervised.
#
# Laravel's schedule:work runs inside PM2 too, so no crontab is needed anywhere.
# ---------------------------------------------------------------------------

if [ "${RUN_WORKERS}" = "true" ]; then
    log "Starting php-fpm, queue worker and scheduler under PM2"
    exec pm2-runtime start "${APP_DIR}/docker/pm2.config.cjs"
fi

warn "RUN_WORKERS=false -- serving web only, no queue worker or scheduler"
exec php-fpm -F
