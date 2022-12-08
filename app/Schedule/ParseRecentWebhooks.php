<?php

namespace App\Schedule;

use App\Models\Crons\LeadCron;

class ParseRecentWebhooks
{
    public function __invoke()
    {
        LeadCron::parseRecentWebhooks();
    }
}
