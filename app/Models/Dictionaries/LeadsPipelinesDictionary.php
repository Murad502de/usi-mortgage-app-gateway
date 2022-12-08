<?php

namespace App\Models\Dictionaries;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class LeadsPipelinesDictionary extends Model
{
    use HasFactory;

    public function __construct()
    {
        Log::info(__METHOD__); //DELETE
    }

    public static function fetchPipelines()
    {
        Log::info(__METHOD__); //DELETE
    }
}
