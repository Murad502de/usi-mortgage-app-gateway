<?php

namespace App\Services\amoAPI\amoHttp;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;

class amoClient
{
    private $requestLimit;
    private $requestCounter;
    private $requestDelay;
    private $client;
    private $errors = [
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable',
    ];

    function __construct()
    {
        $this->requestCounter = 0;
        $this->requestLimit = 4;
        $this->requestDelay = 0;
        $this->client = new Client();
    }

    public function sendRequest( $requestData = null )
    {
        if ( !$requestData ) return;

        if ( $this->requestCounter >= $this->requestLimit )
        {
            Log::info(
                __METHOD__,

                [
                    'message'  => 'request limit exceeded, requestCounter: ' . $this->requestCounter
                ]
            );

            $this->requestDelay = 500;
            $this->requestCounter = 0;

            usleep( 500000 ); // FIXME es muss später entfernt werden
        }
        else
        {
            $this->requestDelay = 0;
            $this->requestCounter++;
        }

        try
        {
            $response = $this->client->request(
                $requestData[ 'method' ],
                $requestData[ 'url' ],
                [
                    'headers' => $requestData[ 'headers' ],
                    'json'    => $requestData[ 'data' ] ?? null,
                    'query' => $requestData[ 'query' ] ?? null,
                    //'delay' => $this->requestDelay // FIXME
                ]
            );

            return [
                'body' => json_decode( $response->getBody(), true ),
                'code' => ( int ) $response->getStatusCode()
            ];
        }
        catch( \Exception $e )
        {
            Log::error(
                __METHOD__,

                [
                    'message'  => "Error while sending request\r\n" .
                                    "error code: " . $e->getCode() . "\r\n" .
                                    "error message: " . $e->getMessage() . "\r\n" .
                                    "request link: " . $requestData[ 'url' ] . "\r\n" .
                                    "request data: " . \print_r( $requestData, true ) . "\r\n"
                ]
            );

            return [
                'code' => ( int ) $e->getCode()
            ];
        }
    }

    /* =================================PUBLIC==METHODS================================= */

    public function accessTokenUpdate( $data )
    {
        $response = $this->sendRequest(
            [
                'url'     => 'https://' . $data[ 'subdomain' ] . '.amocrm.ru/oauth2/access_token',
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'method'  => 'POST',
                'data'    => [
                    'client_id'     => $data[ 'client_id' ],
                    'client_secret' => $data[ 'client_secret' ],
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $data[ 'refresh_token' ],
                    'redirect_uri'  => $data[ 'redirect_uri' ],
                ]
            ]
        );

        return $response;
    }
}
