<?php

namespace App\Schedule\Webhooks;

use App\Models\Webhooks\ChangeStageWebhook;

class ChangeStageWebhooks
{
    public function __invoke()
    {
        ChangeStageWebhook::parseRecentWebhooks();
    }
}
