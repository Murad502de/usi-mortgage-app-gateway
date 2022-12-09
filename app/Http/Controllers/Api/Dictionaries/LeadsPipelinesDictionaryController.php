<?php

namespace App\Http\Controllers\Api\Dictionaries;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\Dictionaries\PipelinesDictionaryResource;
use App\Models\Dictionaries\LeadsPipelinesDictionary;

class LeadsPipelinesDictionaryController extends Controller
{
    public function pipelines()
    {
        Log::info(__METHOD__); //DELETE

        $pipelines = LeadsPipelinesDictionary::all();

        return PipelinesDictionaryResource::collection($pipelines);
    }
}
