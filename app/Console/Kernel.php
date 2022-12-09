<?php

namespace App\Console;

// use App\Schedule\ParseRecentWebhooks;
use App\Schedule\Dictionaries\FetchLeadsPipelines;
use App\Schedule\Dictionaries\FetchUsers;
use App\Schedule\StartQueueProcessing;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // $schedule->call(new FetchLeadsPipelines)
        //     ->name('fetch_leads_pipelines_dictionary')
        //     ->withoutOverlapping()
        //     ->everyMinute(); //TODO: every 5 min

        $schedule->call(new FetchUsers)
            ->name('fetch_users_dictionary')
            ->withoutOverlapping()
            ->everyMinute(); //TODO: every 5 min

        // $schedule->call(new ParseRecentWebhooks)
        //     ->name('parse_recent_webhooks')
        //     ->withoutOverlapping()
        //     ->everyMinute();

        $schedule->exec((new StartQueueProcessing)(true))
            ->name('start_queue_processing')
            ->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
