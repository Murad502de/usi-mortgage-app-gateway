<?php

namespace App\Providers;

class QueueServiceProvider extends \MichaelLedin\LaravelQueueRateLimit\QueueServiceProvider
{
    protected function registerLogger()
    {
        $this->app->singleton('queue.logger', function () {
            return null;
        });
    }
}
