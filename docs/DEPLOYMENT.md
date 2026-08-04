# Running queues and the scheduler on the server

MemFlash needs two background processes in production:

| Process | What it does | Without it |
|---|---|---|
| **Queue worker** | Runs jobs, currently FSRS parameter optimization | Jobs pile up in the `jobs` table and never run. Nothing else breaks. |
| **Scheduler** | Fires cron-driven tasks (`fsrs:optimize`, queue pruning) | Parameters are never refitted; `job_batches` and `failed_jobs` grow forever. |

Neither is required for studying. Reviews are scheduled synchronously in the
request, so the app is fully usable with both stopped — which also means you can
deploy them after the app is already live.

---

## 1. Check what you are running

```bash
php artisan about --only=drivers
```

Queue and cache are on the `database` driver, so there is **nothing extra to
install** — no Redis, no Beanstalk. Jobs live in the `jobs` table created by the
default migrations.

Confirm the tables exist:

```bash
php artisan migrate --force
php artisan queue:monitor default --max=100   # non-zero exit if the queue is backing up
```

---

## 2. The scheduler: exactly one cron entry

Laravel does its own scheduling. The server needs **one** cron line, running every
minute — not one line per task:

```cron
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

Install it for the user that owns the application files (matching the web user
avoids permission problems on `storage/`):

```bash
sudo crontab -u www-data -e
```

Verify:

```bash
php artisan schedule:list
```

You should see:

```
30 3 * * 1  php artisan fsrs:optimize
0  0 * * *  php artisan queue:prune-batches --hours=48
0  0 * * 0  php artisan queue:prune-failed --hours=168
```

Tasks are defined in `routes/console.php`, not in crontab. Add new ones there.

### Inside Docker

The `app` container runs php-fpm as PID 1 and has no cron daemon, so put the entry
on the **host** and exec into the container:

```cron
* * * * * cd /path/to/MemFlash && docker compose exec -T app php artisan schedule:run >> /dev/null 2>&1
```

`-T` matters: without it Docker allocates a TTY and cron fails with
`the input device is not a TTY`.

---

## 3. The queue worker: keep it running

`php artisan queue:work` exits on error, on `queue:restart`, and when it hits its
memory limit. It must be supervised so it comes back.

### Option A — systemd (recommended outside Docker)

`/etc/systemd/system/memflash-worker.service`:

```ini
[Unit]
Description=MemFlash queue worker
After=network.target postgresql.service

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=5
WorkingDirectory=/var/www/html

# --timeout must exceed the job's own timeout (900s in OptimizeFsrsParameters),
# or the worker kills a legitimate long optimization run mid-flight.
ExecStart=/usr/bin/php /var/www/html/artisan queue:work \
    --queue=default \
    --sleep=3 \
    --tries=1 \
    --timeout=960 \
    --max-time=3600

# Recycle hourly so a leaked reference cannot grow unbounded.
StandardOutput=append:/var/log/memflash/worker.log
StandardError=append:/var/log/memflash/worker.log

[Install]
WantedBy=multi-user.target
```

```bash
sudo mkdir -p /var/log/memflash && sudo chown www-data:www-data /var/log/memflash
sudo systemctl daemon-reload
sudo systemctl enable --now memflash-worker
sudo systemctl status memflash-worker
```

One worker is plenty. Optimization is weekly and CPU-bound; a second worker would
just compete for the same cores.

### Option B — Supervisor

The Dockerfile already installs nothing for this, but the entrypoint has a
commented `supervisord` line, so this is the path of least surprise if you want
both php-fpm and a worker in one container.

`/etc/supervisor/conf.d/memflash-worker.conf`:

```ini
[program:memflash-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --tries=1 --timeout=960 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/memflash/worker.log
stopwaitsecs=980
```

`stopwaitsecs` must exceed the job timeout, or Supervisor SIGKILLs a running
optimization during a deploy.

```bash
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl start memflash-worker:*
```

### Option C — a separate compose service

Cleanest for Docker: reuse the app image with a different command.

```yaml
  worker:
    image: articles
    container_name: ${CONTAINER_NAME}_worker
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
    # Bypass the entrypoint: it runs migrations and would race the app container.
    entrypoint: ["php", "artisan", "queue:work", "--tries=1", "--timeout=960", "--max-time=3600"]
    depends_on:
      - db
    networks:
      - net
```

The `entrypoint` override is the important part — the default entrypoint runs
migrations, and two containers migrating at once can deadlock.

---

## 4. Deploys must restart the worker

A running worker holds **old code in memory**. After deploying:

```bash
php artisan queue:restart
```

This asks workers to finish the current job and exit; the supervisor restarts them
on the new code. Without it a worker can run last week's code indefinitely.

Order matters:

```bash
php artisan down                # optional
git pull && composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize            # cache config, routes, views
php artisan queue:restart       # AFTER optimize
php artisan up
```

> **Never run the test suite on the server after `optimize`.** A cached config
> makes Laravel ignore `phpunit.xml`, so `RefreshDatabase` would migrate and
> truncate the **production** database. `Tests\TestCase` now refuses to run in that
> state, but do not rely on it — run tests in CI.

---

## 5. Monitoring

```bash
# Pending and failed work
php artisan queue:monitor default --max=100
php artisan queue:failed

# Did the scheduler actually fire?
grep fsrs:optimize /var/log/memflash/worker.log
tail -f storage/logs/laravel.log | grep FSRS
```

A successful optimization logs:

```
FSRS optimization complete {"deck":"1:personal:3","reviews":812,
  "log_loss":"0.34112 -> 0.31908","rmse":"0.04211 -> 0.02887","improved":true}
```

`improved:false` means the fit was no better than the defaults and the defaults
were kept. That is expected on small or very consistent histories.

Retry a failed job after fixing the cause:

```bash
php artisan queue:retry all
```

---

## 6. Optimization in practice

Parameters are fitted **per deck**, from that deck's own review log.

- Below **400** usable reviews the command skips the deck. Fitting fewer produces
  confident nonsense.
- **1000+** gives a reliable fit; between 400 and 1000 the command warns.
- "Usable" excludes each card's first review (nothing to predict from) and
  same-day repeats (only the first review of a card per day counts).

Trigger it manually:

```bash
php artisan fsrs:optimize --dry-run           # who is eligible, changes nothing
php artisan fsrs:optimize                     # queue for every eligible deck
php artisan fsrs:optimize --sync --deck=3     # run now and print the fit
php artisan fsrs:optimize --user=1
```

`--sync` bypasses the queue, so you do not need a worker to try it.

Because `review_logs` is append-only, optimization can always be re-run and never
loses history. If a fit makes scheduling worse, reset that deck's `parameters` to
`App\Fsrs\Parameters::DEFAULTS` and the next run starts fresh.

---

## 7. Troubleshooting

| Symptom | Cause |
|---|---|
| Jobs stay `pending` forever | No worker running. `systemctl status memflash-worker`. |
| `the input device is not a TTY` | Missing `-T` on `docker compose exec` in cron. |
| Worker runs old code after deploy | Missing `php artisan queue:restart`. |
| Optimization killed part-way | Worker `--timeout` below the job's 900s. |
| `fsrs:optimize` skips everything | Fewer than 400 usable reviews per deck. Check `php artisan tinker --execute='echo App\Models\ReviewLog::count();'` |
| Scheduler never fires | Cron installed for the wrong user, or the path in the crontab is wrong. Test with `php artisan schedule:run` by hand. |
| `UniqueConstraintViolation` on `jobs` | Two schedulers running. `onOneServer()` needs a shared cache — with the `database` cache driver that is already satisfied. |
