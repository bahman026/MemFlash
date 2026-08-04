# Deployment

**There is nothing to set up by hand.** `docker/entrypoint.sh` prepares the
application on every container start and then hands PID 1 to `pm2-runtime`, which
supervises three processes:

| PM2 app | Command | Purpose |
|---|---|---|
| `php-fpm` | `php-fpm -F` | Serves requests |
| `queue` | `artisan queue:work` | Runs jobs (FSRS optimization) |
| `scheduler` | `artisan schedule:work` | Replaces cron entirely |

Deploying is:

```bash
git pull
docker compose up -d --build
```

That is the whole procedure. No crontab, no systemd unit, no Supervisor config.

---

## Why there is no cron entry

`artisan schedule:work` is a long-running process that invokes `schedule:run` every
minute by itself. Because PM2 keeps it alive, the usual
`* * * * * php artisan schedule:run` line is unnecessary — and so is a cron daemon
inside the container, which the `php:8.4-fpm` base image does not have.

Scheduled tasks are declared in `routes/console.php`, not in a crontab:

```bash
docker compose exec app php artisan schedule:list
```

```
30 3 * * 1  php artisan fsrs:optimize
0  0 * * *  php artisan queue:prune-batches --hours=48
0  0 * * 0  php artisan queue:prune-failed --hours=168
```

---

## What the entrypoint does, in order

1. `composer install`
2. Generates `APP_KEY` **only if empty** — without it every session-touching
   request fails with `MissingAppKeyException` and only `/up` answers
3. **Waits for PostgreSQL.** `depends_on` waits for the container, not for Postgres
   to accept connections, so migrating immediately is a race on a cold boot
4. `migrate --force` — pending migrations only, never `migrate:fresh`
5. Seeds **only if `static_decks` is empty**
6. Builds front-end assets if the Vite manifest is missing
7. Removes a stale `public/hot`, which would make every page render unstyled
8. `storage:link`, then `optimize`
9. `exec pm2-runtime start docker/pm2.config.cjs`

Every step is idempotent and non-destructive. It runs on restarts of a live
server, so it must never drop data — which is why step 4 is `migrate`, not
`migrate:fresh`.

---

## Configuration

All defaults are safe for production. Set these in `.env` or the compose
environment.

| Variable | Default | Effect |
|---|---|---|
| `RUN_WORKERS` | `true` | `false` serves web only — no queue, no scheduler |
| `RUN_MIGRATIONS` | `true` | Apply pending migrations on boot |
| `SEED_IF_EMPTY` | `true` | Seed only when there is no curriculum content |
| `BUILD_ASSETS` | `missing` | `always` \| `missing` \| `never` |
| `INSTALL_DEPS` | `true` | `composer install` on boot |
| `CACHE_CONFIG` | `true` | `artisan optimize` |
| `DB_WAIT_TIMEOUT` | `60` | Seconds to wait for the database |
| `DB_FRESH_ON_BOOT` | `false` | ⚠️ **`true` destroys all data.** Local dev only |

### Scaling web separately from workers

Running everything in one container is the trade for zero manual management. To
split them, run a second service from the same image with `RUN_WORKERS=false` on
the web one:

```yaml
  worker:
    image: articles
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
    environment:
      # Only one container should migrate; two racing can deadlock.
      RUN_MIGRATIONS: "false"
      SEED_IF_EMPTY: "false"
      BUILD_ASSETS: "never"
    depends_on: [db]
    networks: [net]
```

Both then run the same PM2 stack; set `RUN_WORKERS=false` on `app` so the queue is
not consumed twice.

---

## Operating it

```bash
# Process health. The ↺ column is restart count -- persistently non-zero means
# something is crash-looping.
docker compose exec app pm2 list

docker compose exec app pm2 describe queue
docker compose exec app pm2 logs queue --lines 100
docker compose exec app pm2 logs scheduler --lines 50

# Restart one process without touching the others
docker compose exec app pm2 restart queue

# Queue state
docker compose exec app php artisan queue:monitor default --max=100
docker compose exec app php artisan queue:failed
docker compose exec app php artisan queue:retry all
```

### Timeouts must stay ordered

```
job timeout (900s)  <  worker --timeout (960s)  <  PM2 kill_timeout (980s)
```

`OptimizeFsrsParameters::$timeout` is 900s. If the worker's `--timeout` were lower
the worker would kill its own job; if PM2's `kill_timeout` were lower, PM2 would
SIGKILL a running optimization during a restart. All three live in
`docker/pm2.config.cjs`, derived from one constant — change it there, not in three
places.

### Deploys and stale code

A running worker holds old code in memory. `docker compose up -d --build` replaces
the container so this resolves itself. If you ever reload code **without**
recreating the container, run:

```bash
docker compose exec app php artisan queue:restart
```

---

## Logs

| What | Where |
|---|---|
| Application | `storage/logs/laravel.log` |
| PM2 + all three processes | `docker compose logs app` |
| php-fpm errors | `storage/logs/php-fpm.log` |
| php-fpm requests | `storage/logs/php-fpm-access.log` |
| nginx | `docker compose logs webserver` |

php-fpm logs to files rather than stdout because the base image's `docker.conf`
points `error_log` and `access.log` at `/proc/self/fd/2`. Under PM2 the child's
stderr is a pipe, and php-fpm cannot open it — it fails at startup with
`failed to open error_log (/proc/self/fd/2): No such device or address`.
`docker/php/zzz-logs.conf` redirects both. Fatal startup messages are written to
stderr *before* that log opens, so PM2 still captures them and they remain visible
in `docker compose logs`.

A successful optimization logs:

```
FSRS optimization complete {"deck":"1:personal:3","reviews":812,
  "log_loss":"0.34112 -> 0.31908","rmse":"0.04211 -> 0.02887","improved":true}
```

`improved:false` means the fit was no better than the defaults, and the defaults
were kept. That is expected on small or very consistent histories.

---

## Optimization in practice

Parameters are fitted **per deck** from that deck's own review log.

- Below **400** usable reviews the deck is skipped; a fit on less is confident nonsense
- **1000+** is reliable; between 400 and 1000 the command warns
- "Usable" excludes each card's first review (nothing to predict from) and same-day
  repeats (only the first review of a card per day counts)

```bash
docker compose exec app php artisan fsrs:optimize --dry-run       # who is eligible
docker compose exec app php artisan fsrs:optimize                 # queue everything eligible
docker compose exec app php artisan fsrs:optimize --sync --deck=3 # run now, print the fit
```

`--sync` bypasses the queue, so it works even with `RUN_WORKERS=false`.

`review_logs` is append-only, so optimization can always be re-run and never loses
history. If a fit makes scheduling worse, reset that deck's `parameters` to
`App\Fsrs\Parameters::DEFAULTS` and the next run starts fresh.

---

## Never run the test suite on the server

With a cached config Laravel ignores `phpunit.xml`, so `RefreshDatabase` migrates
and truncates whatever database the cache names — **production**. The entrypoint
runs `artisan optimize`, so a deployed container is always in that state.

`Tests\TestCase` refuses to run when the config is cached or the database is not
`memflash_test`, but treat that as a backstop. Run tests in CI.

---

## Troubleshooting

| Symptom | Cause |
|---|---|
| Jobs stay pending | `pm2 list` — is `queue` online? Check `RUN_WORKERS`. |
| `php-fpm` restart count climbing | Read `docker compose logs app`. A config error in `php-fpm.d/` fails at startup. |
| `failed to open error_log (/proc/self/fd/2)` | `docker/php/zzz-logs.conf` missing from the image. Rebuild. |
| Scheduler never fires | `pm2 describe scheduler`. Args must be `schedule:work`. |
| Optimization killed part-way | A timeout out of order — see above. |
| `fsrs:optimize` skips everything | Fewer than 400 usable reviews per deck. |
| Unstyled pages | Stale `public/hot`, or no Vite manifest. The entrypoint handles both; try `BUILD_ASSETS=always`. |
| `MissingAppKeyException` | `APP_KEY` empty **and** `.env` not writable, so the entrypoint could not generate one. |
| Entrypoint change has no effect | It is baked into the image (`Dockerfile`), not the volume. Rebuild with `--build`. |
| PM2 config throws on `module.exports` | The file must stay `.cjs`; `package.json` declares `"type": "module"`. |

---

## Without Docker

The entrypoint assumes a container. On bare metal, install PM2 and use the same
config, which is not Docker-specific:

```bash
npm install -g pm2@6
cd /var/www/html
pm2 start docker/pm2.config.cjs
pm2 save                  # restore the process list after a reboot
pm2 startup               # prints the systemd command to run for boot persistence
```

`pm2 save` plus `pm2 startup` replaces a systemd unit per process. Everything in
the operating and troubleshooting sections applies, minus `docker compose exec`.
