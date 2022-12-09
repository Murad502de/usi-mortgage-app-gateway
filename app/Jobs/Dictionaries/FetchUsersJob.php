<?php

namespace App\Jobs\Dictionaries;

use App\Jobs\Middleware\AmoTokenExpirationControl;
use App\Models\Dictionaries\UsersDictionary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        Log::info(__METHOD__); //DELETE

        UsersDictionary::fetchUsers();
    }

    public function middleware()
    {
        return [new AmoTokenExpirationControl];
    }
}
