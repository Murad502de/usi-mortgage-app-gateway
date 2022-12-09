<?php

namespace App\Http\Controllers\Api\Dictionaries;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadsUsersDictionaryController extends Controller
{
    public function users()
    {
        Log::info(__METHOD__); //DELETE

        return true;
    }
}
