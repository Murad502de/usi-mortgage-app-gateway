<?php

namespace App\Http\Controllers\Api\Dictionaries;

use App\Http\Controllers\Controller;
use App\Http\Resources\Dictionaries\UsersDictionaryResource;
use App\Models\Dictionaries\UsersDictionary;
use Illuminate\Support\Facades\Log;

class LeadsUsersDictionaryController extends Controller
{
    public function users()
    {
        Log::info(__METHOD__); //DELETE

        $users = UsersDictionary::all();

        return UsersDictionaryResource::collection($users);
    }
}
