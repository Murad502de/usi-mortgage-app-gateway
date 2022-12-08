<?php

namespace App\Schedule;

class StartQueueProcessing
{
    private $localCommand = "cd /var/www/html/ && php artisan queue:work --stop-when-empty";
    private $command      = "cd ~/www/hub.integrat.pro/Murad/x-mara-discount-cards && /opt/php/7.3/bin/php artisan queue:work --stop-when-empty";

    public function __invoke(bool $isLocal = false): string
    {
        return $isLocal ? $this->localCommand : $this->command;
    }
}
