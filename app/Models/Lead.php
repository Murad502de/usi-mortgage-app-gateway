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

    public const LEAD_TYPE_MORTGAGE     = 'mortgage';
    public const LEAD_TYPE_CONSULTATION = 'consultation';
    public static $AMO_API              = null;
    public static $BASIC_LEAD           = null;
    public static $BROKER_ID            = null;
    public static $BROKER_NAME          = null;
    public static $MANAGER_ID           = null;
    public static $MANAGER_NAME         = null;
    public static $CREATED_LEAD_TYPE    = null;
    public static $MESSAGE_FOR_BROKER   = null;
    public static $EXCLUDE_CF           = [];

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

    private static $STAGE_LOSS_ID         = null;
    private static $STAGE_SUCCESS_ID      = null;
    private static $TASK_TYPE_CONTROLL_ID = null;

    /* ENTITY RELATIONS */
    public function lead()
    {
        return $this->hasOne(Lead::class);
    }

    /* CRUD METHODS */
    public static function createLead(array $params): ?int
    {
        Log::info(__METHOD__); //DELETE
        self::initStatic($params);

        if (
            self::$BASIC_LEAD['status_id'] !== self::$STAGE_LOSS_ID &&
            self::$BASIC_LEAD['status_id'] !== self::$STAGE_SUCCESS_ID
        ) {
            $lead = self::whereAmoId($params['lead_amo_id'])->first();

            if (!!$lead && !$lead->is_mortgage) {
                $mortgageLead = $lead->lead;

                if (!!$mortgageLead && !!$mortgageLead->is_mortgage) {
                    $amoMortgageLead = self::fetchLeadById($mortgageLead->amo_id);
                    return self::mortgageExist($amoMortgageLead);
                }

                Log::error(__METHOD__ . ' Mortgage Lead not found for: ' . self::$BASIC_LEAD['id']); //DELETE
                return null;
            }

            Log::info(__METHOD__ . ' Basic Lead not Found: ' . self::$BASIC_LEAD['id']); //DELETE
            return self::mortgageNotExist();
        }

        Log::error(__METHOD__ . ' Basic Lead is closed: ' . self::$BASIC_LEAD['id']); //DELETE
        return null;

        // self::initStatic($params);

        // $contact          = self::parseMainContact(self::$BASIC_LEAD);
        // $mainContact      = self::fetchContactById($contact['id']);
        // $mainContactLeads = self::filterMainContactLeadsById($mainContact['_embedded']['leads'], $params['lead_amo_id']);
        // $mortgageLead     = self::parseMortgageLead($mainContactLeads);

        // if ($mortgageLead) {
        //     return self::mortgageExist($mortgageLead);
        // }

        // return self::mortgageNotExist();
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
    public static function getByAmoId(int $id): ?Lead
    {
        return self::whereAmoId($id)->first();
    }
    public static function getMortgagePipeline(): ?Mortgage
    {
        $pipeline = Pipeline::whereAmoPipelineId(self::$BASIC_LEAD['pipeline_id'])->first();

        return $pipeline ? $pipeline->mortgage : null;
    }
    public static function getTaskTextForMortgage(): string
    {
        if (self::$CREATED_LEAD_TYPE === self::LEAD_TYPE_MORTGAGE) {
            return 'Клиент выбрал квартиру. Хочет открыть ипотеку, свяжись с клиентом';
        }

        if (self::$CREATED_LEAD_TYPE === self::LEAD_TYPE_CONSULTATION) {
            return 'Клиент еще не определился с объектом недвижимости. Нужна консультация';
        }

        return 'Свяжись с клиентом';
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
        $customFields = self::$AMO_API->parseCustomFields(self::$BASIC_LEAD['custom_fields_values'], self::$EXCLUDE_CF);
        $pipelineId   = (int) self::parseMortgagePipelineId();
        $statusId     = (int) self::parseMortgageCreationStatusId();

        return [
            'name'                 => "Ипотека " . self::$BASIC_LEAD['name'],
            'created_by'           => 0,
            'price'                => self::$BASIC_LEAD['price'],
            'responsible_user_id'  => (int) self::$BROKER_ID,
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
        self::$AMO_API               = new amoAPIHub(amoCRM::getAuthData());
        self::$STAGE_LOSS_ID         = (int) config('services.amoCRM.loss_stage_id');
        self::$STAGE_SUCCESS_ID      = (int) config('services.amoCRM.successful_stage_id');
        self::$BASIC_LEAD            = self::fetchLeadById($params['lead_amo_id']);
        self::$BROKER_ID             = (int) $params['broker_amo_id'];
        self::$BROKER_NAME           = $params['broker_amo_name'];
        self::$MANAGER_ID            = (int) $params['manager_amo_id'];
        self::$MANAGER_NAME          = $params['manager_amo_name'];
        self::$CREATED_LEAD_TYPE     = $params['created_lead_type'];
        self::$MESSAGE_FOR_BROKER    = $params['message_for_broker'];
        self::$TASK_TYPE_CONTROLL_ID = (int) config('services.amoCRM.constant_task_type_id__controll');
        self::$EXCLUDE_CF            = [
            (int) config('services.amoCRM.exclude_cf_utm_source_id'),
            (int) config('services.amoCRM.exclude_cf_utm_medium_id'),
            (int) config('services.amoCRM.exclude_cf_utm_campaign_id'),
            (int) config('services.amoCRM.exclude_cf_utm_term_id'),
            (int) config('services.amoCRM.exclude_cf_utm_content_id'),
            (int) config('services.amoCRM.exclude_cf_roistat_id'),
            (int) config('services.amoCRM.exclude_cf_roistat_marker_id'),
            (int) config('services.amoCRM.exclude_cf_source_id'),
            (int) config('services.amoCRM.exclude_cf_mortgage_created_id'),
            (int) config('services.amoCRM.exclude_cf_broker_selected_id'),
            (int) config('services.amoCRM.exclude_cf_lead_manager_id'),
        ];
    }
    public static function mortgageExist(array $mortgageLead): bool
    {
        // dump($mortgageLead); //DELETE
        Log::info(__METHOD__, [$mortgageLead]); //DELETE

        self::$AMO_API->createTask(
            (int) $mortgageLead['responsible_user_id'],
            (int) $mortgageLead['id'],
            time() + 3600 * 3,
            'Менеджер повторно отправил запрос на ипотеку',
            self::$TASK_TYPE_CONTROLL_ID
        );

        return true;
    }
    public static function mortgageNotExist(): ?int
    {
        Log::info(__METHOD__); //DELETE

        $contacts     = self::prepareContactsForLinking(self::$BASIC_LEAD['_embedded']['contacts']);
        $mortgageLead = self::createMortgageLead();

        self::$AMO_API->linkContactsToLead($mortgageLead['id'], $contacts);
        self::$AMO_API->addTag(self::$BASIC_LEAD['id'], 'Отправлен в Ипотеку');
        self::$AMO_API->addTextNote('leads', $mortgageLead['id'], self::$MESSAGE_FOR_BROKER);
        self::$AMO_API->updateLead([[
            'id'                   => (int) self::$BASIC_LEAD['id'],
            'custom_fields_values' => [
                [
                    'field_id' => (int) config('services.amoCRM.exclude_cf_broker_selected_id'),
                    'values'   => [[
                        'value' => self::$BROKER_NAME,
                    ]],
                ],
                [
                    'field_id' => (int) config('services.amoCRM.exclude_cf_mortgage_created_id'),
                    'values'   => [[
                        'value' => time(),
                    ]],
                ],
            ],
        ]]);
        self::$AMO_API->updateLead([[
            'id'                   => (int) $mortgageLead['id'],
            'custom_fields_values' => [
                [
                    'field_id' => (int) config('services.amoCRM.exclude_cf_lead_manager_id'),
                    'values'   => [[
                        'value' => self::$MANAGER_NAME,
                    ]],
                ],
            ],
        ]]);
        self::$AMO_API->createTask(
            self::$BROKER_ID,
            (int) $mortgageLead['id'],
            time() + 3600,
            self::getTaskTextForMortgage(),
        );

        return self::connectLeadsInRelation($mortgageLead);
    }
    public static function connectLeadsInRelation(array $mortgageLead): ?int
    {
        $basicLeadModel = self::create([
            'amo_id'          => self::$BASIC_LEAD['id'],
            'amo_status_id'   => self::$BASIC_LEAD['status_id'],
            'amo_pipeline_id' => self::$BASIC_LEAD['pipeline_id'],
            'is_mortgage'     => 0,
        ]);
        $mortgageLeadModel = self::create([
            'amo_id'          => $mortgageLead['id'],
            'amo_status_id'   => $mortgageLead['status_id'],
            'amo_pipeline_id' => $mortgageLead['pipeline_id'],
            'is_mortgage'     => 1,
            'lead_id'         => $basicLeadModel->id,
        ]);

        $basicLeadModel->update([
            'lead_id' => $mortgageLeadModel ? $mortgageLeadModel->id : null,
        ]);

        Log::info(__METHOD__, [$mortgageLeadModel ? $mortgageLeadModel->amo_id : null]); //DELETE

        return $mortgageLeadModel ? $mortgageLeadModel->amo_id : null;
    }

    /* ACTIONS-METHODS */
    public static function createMortgageLead(): ?array
    {
        $mortgageLeadData = self::prepareMortgageLeadData();
        $mortgageLeadId   = self::$AMO_API->createLead($mortgageLeadData);

        return $mortgageLeadId ? self::fetchLeadById($mortgageLeadId) : null;
    }

    /* HELPER-METHODS */
    public static function isActive(int $status): bool
    {
        Log::info(__METHOD__, [$status]); //DELETE
        Log::info(__METHOD__, [self::$STAGE_LOSS_ID]); //DELETE
        Log::info(__METHOD__, [self::$STAGE_SUCCESS_ID]); //DELETE

        return $status !== self::$STAGE_LOSS_ID && $status !== self::$STAGE_SUCCESS_ID;
    }
}
