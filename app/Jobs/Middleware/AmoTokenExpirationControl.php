<?php

namespace App\Jobs\Middleware;

use App\Models\Services\amoCRM;
use App\Services\amoAPI\amoHttp\amoClient;
use Illuminate\Support\Facades\Log;

class AmoTokenExpirationControl
{
    /**
     * Process the job in the queue.
     *
     * @param  mixed  $job
     * @param  callable  $next
     * @return mixed
     */
    public function handle($job, $next)
    {
        $client   = new amoClient();
        $authData = amoCRM::getAuthData();

        if ($authData) {
            if (time() >= (int) $authData['when_expires']) {
                // Log::info(__METHOD__, ['access token expired']); //DELETE

                $response = $client->accessTokenUpdate($authData);

                if ($response['code'] >= 200 && $response['code'] < 204) {
                    $accountData = [
                        'client_id'     => $authData['client_id'],
                        'client_secret' => $authData['client_secret'],
                        'subdomain'     => $authData['subdomain'],
                        'access_token'  => $response['body']['access_token'],
                        'redirect_uri'  => $authData['redirect_uri'],
                        'token_type'    => $response['body']['token_type'],
                        'refresh_token' => $response['body']['refresh_token'],
                        'when_expires'  => time() + (int) $response['body']['expires_in'] - 400,
                    ];

                    amoCRM::auth($accountData);

                    // Log::info(__METHOD__, ['access token updated']); //DELETE

                    $next($job);
                } else {
                    // Log::info(__METHOD__, ['Login error with code: ' . $response['code']]); //DELETE

                    $job->release();
                }
            } else {
                // Log::info(__METHOD__, ['access token ist not expired']); //DELETE

                $next($job);
            }
        } else {
            // Log::info(__METHOD__, ['Login data not found']); //DELETE

            $job->release();
        }
    }
}
