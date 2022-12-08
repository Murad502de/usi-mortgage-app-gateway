<?php

namespace App\Console;

// use App\Schedule\ParseRecentWebhooks;
// use App\Schedule\StartQueueProcessing;
use App\Schedule\Dictionaries\FetchLeadsPipelines;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(new FetchLeadsPipelines)
            ->name('fetch_leads_pipelines')
            ->withoutOverlapping()
            ->everyMinute();

        // $schedule->call(new ParseRecentWebhooks)
        //     ->name('parse_recent_webhooks')
        //     ->withoutOverlapping()
        //     ->everyMinute();
        // $schedule->exec((new StartQueueProcessing)(true))
        //     ->name('start_queue_processing')
        //     ->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
