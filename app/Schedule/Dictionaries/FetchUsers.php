<?php

namespace App\Schedule\Dictionaries;

use App\Jobs\Dictionaries\FetchUsersJob;
use Illuminate\Support\Facades\Log;

class FetchUsers
{
    public function __invoke()
    {
        Log::info(__METHOD__); //DELETE

        FetchUsersJob::dispatch();
    }
}
