<?php

namespace App\Http\Controllers\Api\Dictionaries;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class LeadsPipelinesDictionaryController extends Controller
{
    public function pipelines()
    {
        Log::info(__METHOD__); //DELETE

        return true;
    }
}
