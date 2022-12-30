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

    public static $AMO_API            = null;
    public static $BASIC_LEAD         = null;
    public static $BROKER_ID          = null;
    public static $BROKER_NAME        = null;
    public static $CREATED_LEAD_TYPE  = null;
    public static $MESSAGE_FOR_BROKER = null;

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
    public static function createLead(array $params): bool
    {
        Log::info(__METHOD__); //DELETE

        self::initStatic($params);

        $contact          = self::parseMainContact(self::$BASIC_LEAD);
        $mainContact      = self::fetchContactById($contact['id']);
        $mainContactLeads = self::filterMainContactLeadsById($mainContact['_embedded']['leads'], $params['lead_amo_id']);
        $mortgageLead     = self::parseMortgageLead($mainContactLeads);

        if ($mortgageLead) {
            return self::mortgageExist($mortgageLead);
        }

        return self::mortgageNotExist();
    }
    public function updateLead(array $lead)
    {
        return $this->update(array_merge($lead, []));
    }

    /* GETTERS-METHODS */
    public static function getByUuid(string $uuid): ?Lead
    {
        return self::whereUuid($uuid)->first();
    }
    public static function getMortgagePipeline(): ?Mortgage
    {
        $pipeline = Pipeline::whereAmoPipelineId(self::$BASIC_LEAD['pipeline_id'])->first();

        return $pipeline ? $pipeline->mortgage : null;
    }

    /* FETCH-METHODS */
    public static function fetchLeadById(int $id): array
    {
        $findLeadByIdResponse = self::$AMO_API->findLeadById($id);

        if ($findLeadByIdResponse['code'] !== Response::HTTP_OK) {
            throw new NotFoundException('lead not found by id: ' . $id);
        }

        return $findLeadByIdResponse['body'];
    }
    public static function fetchContactById(int $id): array
    {
        $findLeadByIdResponse = self::$AMO_API->findContactById($id);

        if ($findLeadByIdResponse['code'] !== Response::HTTP_OK) {
            throw new NotFoundException('main contact not found');
        }

        return $findLeadByIdResponse['body'];
    }

    /* PARSE-METHODS */
    public static function parseMainContact(array $lead): array
    {
        $contacts = $lead['_embedded']['contacts'];

        for ($contactIndex = 0; $contactIndex < count($contacts); $contactIndex++) {
            if ($contacts[$contactIndex]['is_main']) {
                return $contacts[$contactIndex];
            }
        }

        throw new NotFoundException('main contact not parsed');
    }
    public static function parseMortgageLead(array $leads): ?array
    {
        foreach ($leads as $lead) {
            $amoLead = self::fetchLeadById($lead['id']);

            if (Mortgage::whereAmoMortgageId($amoLead['pipeline_id'])->first()) {
                return $amoLead;
            }
        }

        return null;
    }
    public static function parseMortgagePipelineId(): ?int
    {
        $mortgage = self::getMortgagePipeline();

        if ($mortgage) {
            return $mortgage->amo_mortgage_id;
        }

        return null;
    }
    public static function parseMortgageCreationStatusId(): ?int
    {
        $mortgage = self::getMortgagePipeline();

        if ($mortgage) {
            return $mortgage->amo_mortgage_creation_stage_id;
        }

        return null;
    }

    /* FUNCTIONS-METHODS */
    public static function prepareContactsForLinking(array $contacts): array
    {
        Log::info(__METHOD__); //DELETE

        $linkingContacts = [];

        for ($i = 0; $i < count($contacts); $i++) {
            $linkingContacts[] = [
                "to_entity_id"   => $contacts[$i]['id'],
                "to_entity_type" => "contacts",
                "metadata"       => [
                    "is_main" => $contacts[$i]['is_main'] ? true : false,
                ],
            ];
        }

        return $linkingContacts;
    }
    public static function prepareMortgageLeadData(): array
    {
        $customFields = self::$AMO_API->parseCustomFields(self::$BASIC_LEAD['custom_fields_values']);
        $pipelineId   = self::parseMortgagePipelineId();
        $statusId     = self::parseMortgageCreationStatusId();

        return [
            'name'                 => "Ипотека " . self::$BASIC_LEAD['name'],
            'created_by'           => 0,
            'price'                => self::$BASIC_LEAD['price'],
            'responsible_user_id'  => self::$BROKER_ID,
            'status_id'            => $statusId,
            'pipeline_id'          => $pipelineId,
            'custom_fields_values' => $customFields,
        ];
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

    /* PROCEDURES-METHODS */
    public static function initStatic(array $params)
    {
        self::$AMO_API            = new amoAPIHub(amoCRM::getAuthData());
        self::$BASIC_LEAD         = self::fetchLeadById($params['lead_amo_id']);
        self::$BROKER_ID          = $params['broker_amo_id'];
        self::$BROKER_NAME        = $params['broker_amo_name'];
        self::$CREATED_LEAD_TYPE  = $params['created_lead_type'];
        self::$MESSAGE_FOR_BROKER = $params['message_for_broker'];
    }
    public static function mortgageExist(array $mortgageLead): bool
    {
        Log::info(__METHOD__, [$mortgageLead]); //DELETE

        return true;
    }
    public static function mortgageNotExist(): bool
    {
        Log::info(__METHOD__); //DELETE

        $contacts             = self::prepareContactsForLinking(self::$BASIC_LEAD['_embedded']['contacts']);
        $mortgageLead         = self::createMortgageLead();
        $linkContactsResponse = self::$AMO_API->linkContactsToLead($mortgageLead['id'], $contacts);

        Log::info(__METHOD__, [$linkContactsResponse]); //DELETE

        //TODO: add leads in DB and make relation

        return true;
    }

    /* ACTIONS-METHODS */
    public static function createMortgageLead(): ?array
    {
        $mortgageLeadData = self::prepareMortgageLeadData();
        $mortgageLeadId   = self::$AMO_API->createLead($mortgageLeadData);

        return $mortgageLeadId ? self::fetchLeadById($mortgageLeadId) : null;
    }
}
