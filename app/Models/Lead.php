<?php

namespace App\Models;

use App\Exceptions\NotFoundException;
use App\Models\Services\amoCRM;
use App\Services\amoAPI\amoAPIHub;
use App\Traits\Model\generateUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

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
        self::$AMO_API    = new amoAPIHub(amoCRM::getAuthData());
        $leads            = self::fetchLeadById($params['lead_amo_id']);
        $contact          = self::parseMainContact($leads);
        $mainContact      = self::fetchContactById($contact['id']);
        $mainContactLeads = self::filterMainContactLeadsById($mainContact['_embedded']['leads'], $params['lead_amo_id']);

        Log::info(__METHOD__, [$mainContactLeads]); //DELETE

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

    /* FETCH-METHODS */
    public static function fetchLeadById(int $id): array
    {
        // Log::info(__METHOD__, [$id]); //DELETE

        $findLeadByIdResponse = self::$AMO_API->findLeadById($id);

        // Log::info(__METHOD__, [$findLeadByIdResponse]); //DELETE

        if ($findLeadByIdResponse['code'] !== Response::HTTP_OK) {
            throw new NotFoundException('basic lead not found');
        }

        return $findLeadByIdResponse['body'];
    }
    public static function fetchContactById(int $id): array
    {
        // Log::info(__METHOD__, [$id]); //DELETE

        $findLeadByIdResponse = self::$AMO_API->findContactById($id);

        // Log::info(__METHOD__, [$findLeadByIdResponse]); //DELETE

        if ($findLeadByIdResponse['code'] !== Response::HTTP_OK) {
            throw new NotFoundException('main contact not found');
        }

        return $findLeadByIdResponse['body'];
    }

    /* PARSE-METHODS */
    public static function parseMainContact(array $lead): array
    {
        // Log::info(__METHOD__, [$lead]); //DELETE

        $contacts = $lead['_embedded']['contacts'];

        for ($contactIndex = 0; $contactIndex < count($contacts); $contactIndex++) {
            if ($contacts[$contactIndex]['is_main']) {
                return $contacts[$contactIndex];
            }
        }

        throw new NotFoundException('main contact not parsed');
    }

    /* FILTER-METHODS */
    public static function filterMainContactLeadsById(array $leads, int $id): array
    {
        $filteredLeads = [];

        foreach ($leads as $lead) {
            if ($lead['id'] !== $id) {
                $filteredLeads[] = $lead;
            }
        }

        return $filteredLeads;
    }
}
