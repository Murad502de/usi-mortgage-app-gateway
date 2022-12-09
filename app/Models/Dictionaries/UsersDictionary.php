<?php

namespace App\Models\Dictionaries;

use App\Models\Services\amoCRM;
use App\Services\amoAPI\amoAPIHub;
use App\Traits\Model\generateUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Illuminate\Support\Facades\Log;

class UsersDictionary extends Model
{
    use HasFactory;
    use generateUuid;

    protected $fillable = [
        'amo_id',
        'name',
        'uuid',
    ];
    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    public static function fetchUsers()
    {
        // Log::info(__METHOD__); //DELETE

        $amoAPI   = new amoAPIHub(amoCRM::getAuthData());
        $response = $amoAPI->fetchUsers();

        if ($response) {
            foreach (self::all() as $user) {
                $user->delete();
            }

            foreach ($response['body']['_embedded']['users'] as $user) {
                // Log::info(__METHOD__, [$user['id'] . ' : ' . $user['name']]); //DELETE

                $createdUser = self::create([
                    'amo_id' => (int) $user['id'],
                    'name'   => $user['name'],
                ]);

                // Log::info(__METHOD__, ['createdUser']); //DELETE
                // Log::info(__METHOD__, [json_encode($createdUser)]); //DELETE
            }
        }
    }
}
