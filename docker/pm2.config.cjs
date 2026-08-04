/**
 * PM2 process definitions for the app container.
 *
 * pm2-runtime is PID 1, supervising three processes:
 *
 *   php-fpm     serves requests
 *   queue       runs jobs, currently FSRS parameter optimization
 *   scheduler   replaces the cron entry entirely
 *
 * `.cjs` on purpose: package.json declares "type": "module", so a `.js` config
 * would be parsed as an ES module and PM2's `module.exports` would throw.
 *
 * Laravel's schedule:work is a long-running process that invokes schedule:run
 * every minute itself, so no crontab is needed anywhere. That is the main reason
 * this setup can be fully self-managing.
 */

const APP_DIR = '/var/www/html';

/** Keep in sync with OptimizeFsrsParameters::$timeout (900s). */
const JOB_TIMEOUT_SECONDS = 900;
const WORKER_TIMEOUT_SECONDS = JOB_TIMEOUT_SECONDS + 60;

const shared = {
    cwd: APP_DIR,
    exec_mode: 'fork',
    instances: 1,
    autorestart: true,
    // A clean hourly exit must not look like a crash loop.
    min_uptime: '60s',
    max_restarts: 50,
    restart_delay: 2000,
    merge_logs: true,
    time: true,
};

module.exports = {
    apps: [
        {
            ...shared,
            name: 'php-fpm',
            // A binary, not a script, so PM2 must not try to interpret it.
            script: '/usr/local/sbin/php-fpm',
            interpreter: 'none',
            // -F keeps php-fpm in the foreground; daemonising would make PM2 think
            // it exited immediately and restart it forever.
            args: '-F',
        },
        {
            ...shared,
            name: 'queue',
            script: 'artisan',
            interpreter: 'php',
            args: [
                'queue:work',
                '--queue=default',
                '--sleep=3',
                '--tries=1',
                `--timeout=${WORKER_TIMEOUT_SECONDS}`,
                // Recycle hourly so a leaked reference cannot grow unbounded.
                '--max-time=3600',
            ].join(' '),
            /**
             * Milliseconds, and it MUST exceed the job's own timeout. PM2 sends
             * SIGINT, waits kill_timeout, then SIGKILLs. Too low and a legitimate
             * optimization run is killed part-way through.
             */
            kill_timeout: (WORKER_TIMEOUT_SECONDS + 20) * 1000,
        },
        {
            ...shared,
            name: 'scheduler',
            script: 'artisan',
            interpreter: 'php',
            // Replaces `* * * * * php artisan schedule:run`.
            args: 'schedule:work',
            kill_timeout: 30000,
        },
    ],
};
