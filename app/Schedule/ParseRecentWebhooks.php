<?php

namespace App\Schedule;

use App\Schedule\Webhooks\ChangeStageWebhooks;
class ParseRecentWebhooks
{
    public function __invoke()
    {
        (new ChangeStageWebhooks)();
    }
}
