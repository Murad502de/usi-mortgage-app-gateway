<?php

namespace App\Schedule\Dictionaries;

use App\Models\Dictionaries\LeadsPipelinesDictionary;
use Illuminate\Support\Facades\Log;

class FetchLeadsPipelines
{
    private $leadsPipelinesDictionary;

    public function __construct()
    {
        Log::info(__METHOD__); //DELETE

        $this->leadsPipelinesDictionary = new LeadsPipelinesDictionary();
    }

    public function __invoke()
    {
        Log::info(__METHOD__); //DELETE

        $this->leadsPipelinesDictionary->fetchPipelines();
    }
}
