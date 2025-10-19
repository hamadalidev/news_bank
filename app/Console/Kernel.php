<?php

declare(strict_types=1);

namespace App\Console;

use App\Jobs\FetchNewsArticlesJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Fetch news articles every hour
        $schedule->job(new FetchNewsArticlesJob)
            ->hourly()
            ->name('fetch-news-articles')
            ->withoutOverlapping()
            ->runInBackground();

        // You can also add different schedules for different times
        // $schedule->job(new FetchNewsArticlesJob)->everyThirtyMinutes();
        // $schedule->job(new FetchNewsArticlesJob)->dailyAt('08:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}