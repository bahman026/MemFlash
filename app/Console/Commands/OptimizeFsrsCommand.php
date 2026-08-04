<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Fsrs\Optimizer\Optimizer;
use App\Fsrs\Optimizer\TrainingSet;
use App\Jobs\OptimizeFsrsParameters;
use App\Models\Deck;
use App\Models\ReviewLog;
use App\Models\User;
use Illuminate\Console\Command;

class OptimizeFsrsCommand extends Command
{
    protected $signature = 'fsrs:optimize
        {--user= : Restrict to one user id}
        {--deck= : Restrict to one personal deck id}
        {--sync : Run now instead of queueing, and print the fit}
        {--dry-run : Report what would be optimized and stop}';

    protected $description = 'Fit FSRS parameters to logged review history, per deck';

    public function handle(): int
    {
        $users = User::query()
            ->when($this->option('user'), fn ($q, $id) => $q->whereKey($id))
            ->get();

        if ($users->isEmpty()) {
            $this->components->error('No matching users.');

            return self::FAILURE;
        }

        $queued = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $decks = $user->decks()
                ->when($this->option('deck'), fn ($q, $id) => $q->whereKey($id))
                ->get();

            foreach ($decks as $deck) {
                $set = TrainingSet::forDeck($user->id, $deck->id);
                $usable = $set->predictableCount();

                if ($usable < Optimizer::MINIMUM_REVIEWS) {
                    $this->line(sprintf(
                        '  <fg=gray>skip</> %s — %d/%d reviews',
                        $deck->name,
                        $usable,
                        Optimizer::MINIMUM_REVIEWS,
                    ));
                    $skipped++;

                    continue;
                }

                if ($this->option('dry-run')) {
                    $this->line(sprintf('  <fg=green>ready</> %s — %d reviews', $deck->name, $usable));
                    $queued++;

                    continue;
                }

                $job = new OptimizeFsrsParameters($user->id, 'personal', $deck->id);

                if ($this->option('sync')) {
                    $this->optimizeNow($deck, $set);
                } else {
                    dispatch($job);
                    $this->line(sprintf('  <fg=cyan>queued</> %s — %d reviews', $deck->name, $usable));
                }

                $queued++;
            }
        }

        $this->newLine();
        $this->components->info(sprintf(
            '%d deck(s) %s, %d skipped for insufficient history.',
            $queued,
            $this->option('dry-run') ? 'ready' : ($this->option('sync') ? 'optimized' : 'queued'),
            $skipped,
        ));

        if ($skipped > 0) {
            $totalLogged = ReviewLog::count();
            $this->line(sprintf(
                '  <fg=gray>%d reviews logged in total. Optimization needs %d per deck, %d for a reliable fit.</>',
                $totalLogged,
                Optimizer::MINIMUM_REVIEWS,
                Optimizer::RECOMMENDED_REVIEWS,
            ));
        }

        return self::SUCCESS;
    }

    private function optimizeNow(Deck $deck, TrainingSet $set): void
    {
        $this->line("  <fg=cyan>optimizing</> {$deck->name}");

        $result = (new Optimizer($set))->run(function (int $step, float $loss, float $best): void {
            $this->output->write(sprintf("\r    step %2d  loss %.6f  best %.6f", $step, $loss, $best));
        });

        $this->output->write("\r" . str_repeat(' ', 60) . "\r");

        $this->table(['metric', 'before', 'after'], [
            ['log loss', round($result['initial_loss'], 5), round($result['final_loss'], 5)],
            ['RMSE', round($result['initial_rmse'], 5), round($result['final_rmse'], 5)],
        ]);

        $this->line(sprintf(
            '    measured retention %.1f%% over %d reviews on %d cards, %d iterations',
            $result['measured_retention'] * 100,
            $result['reviews'],
            $result['cards'],
            $result['iterations'],
        ));

        if (! $result['improved']) {
            $this->components->warn('    No improvement on the defaults; keeping the defaults.');

            return;
        }

        $deck->configOrDefault()->update([
            'parameters' => $result['parameters'],
            'optimized_at' => now(),
            'optimized_review_count' => $result['reviews'],
        ]);

        if ($result['reviews'] < Optimizer::RECOMMENDED_REVIEWS) {
            $this->components->warn(sprintf(
                '    Fitted on %d reviews; %d gives a more reliable result.',
                $result['reviews'],
                Optimizer::RECOMMENDED_REVIEWS,
            ));
        }
    }
}
