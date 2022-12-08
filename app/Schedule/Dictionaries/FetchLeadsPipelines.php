<?php

namespace App\Schedule\Dictionaries;

use App\Jobs\Dictionaries\FetchLeadsPipelinesJob;
use Illuminate\Support\Facades\Log;

class FetchLeadsPipelines
{
    public function __invoke()
    {
        Log::info(__METHOD__); //DELETE

        FetchLeadsPipelinesJob::dispatch();
        // FetchLeadsPipelinesJob::dispatch();
        // FetchLeadsPipelinesJob::dispatch();
        // FetchLeadsPipelinesJob::dispatch();
        // FetchLeadsPipelinesJob::dispatch();
        // FetchLeadsPipelinesJob::dispatch();
        // FetchLeadsPipelinesJob::dispatch();
        // FetchLeadsPipelinesJob::dispatch();
        // FetchLeadsPipelinesJob::dispatch();
        // FetchLeadsPipelinesJob::dispatch();
    }
}
