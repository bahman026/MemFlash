#!/bin/bash

set -e

APP_DIR="/var/www/html"
ARTISAN="php ${APP_DIR}/artisan"

composer install --no-interaction --optimize-autoloader

# ---------------------------------------------------------------------------
# Application key
#
# Generated only when APP_KEY is empty. Without a key every request that
# touches a cookie or session fails with MissingAppKeyException (HTTP 500),
# and only /up keeps responding.
# ---------------------------------------------------------------------------

if ! grep -qE '^APP_KEY=.+' "${APP_DIR}/.env" 2>/dev/null; then
    echo "==> APP_KEY is empty; generating one."
    ${ARTISAN} key:generate --force
fi

# ---------------------------------------------------------------------------
# Database
#
# This script runs on EVERY container start, so it must never be destructive.
# It previously ran `migrate:fresh --seed --force`, which dropped every table
# on each `docker compose up` and wiped all user decks, cards and progress.
#
# Set DB_FRESH_ON_BOOT=true to opt into a full rebuild. LOCAL DEV ONLY --
# it destroys all data.
# ---------------------------------------------------------------------------

if [ "${DB_FRESH_ON_BOOT:-false}" = "true" ]; then
    echo "==> WARNING: DB_FRESH_ON_BOOT=true -- dropping all tables and reseeding."
    ${ARTISAN} migrate:fresh --seed --force
else
    # Applies pending migrations only. Existing rows are preserved.
    ${ARTISAN} migrate --force

    # Seed curriculum content only when it is absent. The StaticCard*Seeder
    # classes updateOrCreate with interval=1 / revised_at=null / last_reviewed=null,
    # so re-running them rewrites the shared static_cards rows and resets every
    # user's SRS scheduling. Guarding on "are there any static decks?" keeps a
    # fresh database bootstrapping automatically without touching a seeded one.
    NEEDS_SEED="$(php -r '
        require "/var/www/html/vendor/autoload.php";
        $app = require "/var/www/html/bootstrap/app.php";
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        try { echo \App\Models\StaticDeck::count() > 0 ? "no" : "yes"; }
        catch (\Throwable $e) { echo "yes"; }
    ' 2>/dev/null || echo "yes")"

    if [ "${NEEDS_SEED}" = "yes" ]; then
        echo "==> No static decks found; running initial seed."
        ${ARTISAN} db:seed --force
    else
        echo "==> Static decks already present; skipping seed to preserve user data."
    fi
fi

#${ARTISAN} vendor:publish --tag=filament-tables-views --force
# Refresh caches
${ARTISAN} optimize:clear
${ARTISAN} optimize
#${ARTISAN} icon:cache
#${ARTISAN} filament:cache-components

# Create storage symlinks
${ARTISAN} storage:link

# Start Supervisor
#exec supervisord -c /etc/supervisor/supervisord.conf
exec php-fpm
