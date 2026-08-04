<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
|
| Requires ONE cron entry on the server, which runs the scheduler every minute:
|
|   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
|
| Laravel decides from there what is actually due. See docs/DEPLOYMENT.md.
|
*/

// Refit FSRS parameters weekly. Decks with too little history are skipped by the
// command itself, so this is safe to run against every user.
//
// Weekly rather than daily: parameters move slowly, each run replays the entire
// review history, and a user gains nothing from a fit that shifts every night.
Schedule::command('fsrs:optimize')
    ->weeklyOn(1, '03:30')
    ->onOneServer()
    ->withoutOverlapping()
    ->runInBackground()
    ->description('Fit FSRS parameters to logged review history');

// Trim expired queue batch records so the table does not grow without bound.
Schedule::command('queue:prune-batches --hours=48')
    ->daily()
    ->onOneServer();

// Failed jobs are kept a week: long enough to investigate, short enough to bound.
Schedule::command('queue:prune-failed --hours=168')
    ->weekly()
    ->onOneServer();
