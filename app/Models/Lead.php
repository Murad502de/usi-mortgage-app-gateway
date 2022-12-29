<?php

namespace App\Models;

use App\Models\Services\amoCRM;
use App\Services\amoAPI\amoAPIHub;
use App\Traits\Model\generateUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class Lead extends Model
{
    use HasFactory;
    use generateUuid;

    public static $AMO_API = null;

    protected $fillable = [
        'uuid',
        'amo_id',
        'amo_status_id',
        'amo_pipeline_id',
        'is_mortgage',
        'lead_id',
    ];
    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    /* ENTITY RELATIONS */
    public function lead()
    {
        return $this->hasOne(Lead::class);
    }

    /* CRUD METHODS */
    public static function createLead(array $params): ?Lead
    {
        Log::info(__METHOD__, [$params]); //DELETE

        self::$AMO_API = new amoAPIHub(amoCRM::getAuthData());

        $lead = self::fetchLeadById($params['lead_amo_id']);

        return null;
        // return self::create(array_merge($lead, []));
    }
    public static function getByUuid(string $uuid): ?Lead
    {
        return self::whereUuid($uuid)->first();
    }
    public function updateLead(array $lead)
    {
        return $this->update(array_merge($lead, []));
    }

    /* METHODS */
    public static function fetchLeadById(int $id) {
        Log::info(__METHOD__, [$id]); //DELETE

        $findLeadByIdResponse = self::$AMO_API->findLeadById($id);

        dump($findLeadByIdResponse); //DELETE

        // if ($findLeadByIdResponse['code'] !== Response::HTTP_OK) {}
    }
}
