<?php

namespace App\Models\Services;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class amoCRM extends Model
{
    use HasFactory;

    protected $table = 'amocrm_credentials';
    protected $fillable = [
        'client_id',
        'client_secret',
        'subdomain',
        'access_token',
        'redirect_uri',
        'token_type',
        'refresh_token',
        'when_expires',
    ];

    /**
     * @param array $accountData = [
     *  @param string client_id
     *  @param string client_secret
     *  @param string subdomain
     *  @param string access_token
     *  @param string redirect_uri
     *  @param string token_type
     *  @param string refresh_token
     *  @param int when_expires
     * ]
     */
    public static function auth(array $accountData): void
    {
        self::truncate();
        self::create($accountData);
    }
    public static function getAuthData()
    {
        $authData = self::all()->first();

        if (!$authData) {
            return false;
        }

        return [
            'client_id'     => $authData->client_id,
            'client_secret' => $authData->client_secret,
            'subdomain'     => $authData->subdomain,
            'access_token'  => $authData->access_token,
            'redirect_uri'  => $authData->redirect_uri,
            'token_type'    => $authData->token_type,
            'refresh_token' => $authData->refresh_token,
            'when_expires'  => $authData->when_expires,
        ];
    }
}
