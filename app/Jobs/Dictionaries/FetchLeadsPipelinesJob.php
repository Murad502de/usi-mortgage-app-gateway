<?php

namespace App\Jobs\Dictionaries;

use App\Jobs\Middleware\AmoTokenExpirationControl;
use App\Models\Dictionaries\LeadsPipelinesDictionary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchLeadsPipelinesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        Log::info(__METHOD__); //DELETE
    }

    public function handle()
    {
        Log::info(__METHOD__); //DELETE

        LeadsPipelinesDictionary::fetchPipelines();
    }

    public function middleware()
    {
        return [new AmoTokenExpirationControl];
    }
}
