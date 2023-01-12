<?php

namespace App\Models\Webhooks;

use App\Exceptions\NotFoundException;
use App\Models\Lead;
use App\Models\Mortgage;
use App\Models\Pipeline;
use App\Models\Services\amoCRM;
use App\Services\amoAPI\amoAPIHub;
use App\Services\amoAPI\Entities\Lead as LeadEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ChangeStageWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'last_modified',
        'data',
    ];
    protected $hidden = [
        'id',
    ];

    private const PARSE_COUNT             = 40;
    private static $AMO_API               = null;
    private static $STAGE_LOSS_ID         = null;
    private static $STAGE_SUCCESS_ID      = null;
    private static $REJECTION_REASON_ID   = null;
    private static $TASK_TYPE_CONTROLL_ID = null;

    /* ENTITY RELATIONS */

    /* CRUD METHODS */
    public static function createLead(string $leadId, int $lastModified, array $data): void
    {
        self::create([
            'lead_id'       => $leadId,
            'last_modified' => (int) $lastModified,
            'data'          => json_encode($data),
        ]);
    }
    public static function updateLead(string $leadId, int $lastModified, array $data): void
    {
        self::where('lead_id', $leadId)->update([
            'last_modified' => (int) $lastModified,
            'data'          => json_encode($data),
        ]);
    }

    /* GETTERS-METHODS */
    public static function getLeadByAmoId(string $leadId): ?ChangeStageWebhook
    {
        return self::all()->where('lead_id', $leadId)->first();
    }
    public static function getLeadWebhooks()
    {
        return self::orderBy('id', 'asc')
            ->take(self::PARSE_COUNT)
            ->get();
    }
    public static function getLeadWebhookData(ChangeStageWebhook $leadWebhook): array
    {
        return json_decode($leadWebhook->data, true);
    }
    public static function getLeadWebhookStatusId(ChangeStageWebhook $leadWebhook): int
    {
        return (int) self::getLeadWebhookData($leadWebhook)['status_id'];
    }
    public static function getLeadWebhookPipelineId(ChangeStageWebhook $leadWebhook): int
    {
        return (int) self::getLeadWebhookData($leadWebhook)['pipeline_id'];
    }
    public static function getMortgageApplyingStageId(Lead $lead): ?int
    {
        $mortgage = Mortgage::whereAmoMortgageId($lead->amo_pipeline_id)->first();

        return (int) $mortgage->amo_mortgage_applying_stage_id;
    }

    /* HELPER-METHODS */
    public static function isMortgageApproved(Lead $lead): bool
    {
        Log::info(__METHOD__); //DELETE

        return !!Mortgage::whereAmoMortgageId($lead->amo_pipeline_id)
            ->whereAmoMortgageApprovedStageId($lead->amo_status_id)
            ->first();
    }
    public static function isMortgageApplicationSubmited(Lead $lead): bool
    {
        Log::info(__METHOD__); //DELETE

        $mortgage = Mortgage::whereAmoMortgageId($lead->amo_pipeline_id)->first();

        // Log::info(__METHOD__, [$mortgage]); //DELETE

        if (!$mortgage) {
            return false;
        }

        $mortgageAfterApplyingStages   = json_decode($mortgage->amo_mortgage_after_applying_stage_ids, true);
        $mortgageAfterApplyingStages[] = $mortgage->amo_mortgage_applying_stage_id;

        for ($i = 0; $i < count($mortgageAfterApplyingStages); $i++) {
            $mortgageAfterApplyingStages[$i] = (int) $mortgageAfterApplyingStages[$i];
        }

        // Log::info(__METHOD__, [$mortgageAfterApplyingStages]); //DELETE

        $mortgageLead = self::fetchLeadById($lead->amo_id);

        return in_array($mortgageLead['status_id'], $mortgageAfterApplyingStages);
    }
    public static function isBasicLeadBooked(Lead $lead): bool
    {
        Log::info(__METHOD__); //DELETE

        return !!Pipeline::whereAmoPipelineId($lead->amo_pipeline_id)
            ->whereAmoPipelineBookingStageId($lead->amo_status_id)
            ->first();
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

    /* PARSE-METHODS */

    /* FUNCTIONS-METHODS */

    /* FILTER-METHODS */

    /* PROCEDURES-METHODS */
    public static function initStatic()
    {
        self::$AMO_API               = new amoAPIHub(amoCRM::getAuthData());
        self::$STAGE_LOSS_ID         = (int) config('services.amoCRM.loss_stage_id');
        self::$STAGE_SUCCESS_ID      = (int) config('services.amoCRM.successful_stage_id');
        self::$REJECTION_REASON_ID   = (int) config('services.amoCRM.exclude_cf_rejection_reason_id');
        self::$TASK_TYPE_CONTROLL_ID = (int) config('services.amoCRM.constant_task_type_id__controll');
    }
    public static function processWebhook(Lead $lead)
    {
        Log::info(__METHOD__); //DELETE

        if (!!$lead->is_mortgage) {
            return self::processMortgageLead($lead);
        }

        return self::processBasicLead($lead);
    }
    public static function processMortgageLead(Lead $lead)
    {
        Log::info(__METHOD__); //DELETE

        if ($lead->amo_status_id === self::$STAGE_LOSS_ID) {
            return self::processLostMortgageLead($lead);
        }

        if (self::isMortgageApproved($lead)) {
            return self::processApprovedMortgageLead($lead);
        }
    }
    public static function processLostMortgageLead(Lead $lead)
    {
        Log::info(__METHOD__); //DELETE

        $basicLead    = self::fetchLeadById($lead->lead->amo_id);
        $mortgageLead = self::fetchLeadById($lead->amo_id);

        // Log::info(__METHOD__, [$basicLead]); //DELETE
        // Log::info(__METHOD__, [$mortgageLead]); //DELETE

        if (
            $basicLead['status_id'] !== self::$STAGE_LOSS_ID &&
            $basicLead['status_id'] !== self::$STAGE_SUCCESS_ID
        ) {
            // Log::info(__METHOD__, ['target']); //DELETE

            $rejectReason = LeadEntity::findCustomFieldById(
                $mortgageLead['custom_fields_values'],
                self::$REJECTION_REASON_ID
            );

            // Log::info(__METHOD__, [$rejectReason]); //DELETE

            self::$AMO_API->createTask(
                (int) $basicLead['responsible_user_id'],
                (int) $basicLead['id'],
                time() + 10800,
                'Сделку по ипотеке переместили в этап "Закрыто и не реализовано" с причиной отказа: ' . $rejectReason,
                self::$TASK_TYPE_CONTROLL_ID
            );
        }
    }
    public static function processApprovedMortgageLead(Lead $lead)
    {
        Log::info(__METHOD__); //DELETE

        $basicLead = self::fetchLeadById($lead->lead->amo_id);

        self::$AMO_API->createTask(
            (int) $basicLead['responsible_user_id'],
            (int) $basicLead['id'],
            time() + 10800,
            'Клиенту одобрена ипотека',
            self::$TASK_TYPE_CONTROLL_ID
        );
    }
    public static function processMortgageLeadBeforeApplication(Lead $lead)
    {
        Log::info(__METHOD__); //DELETE

        $mortgageLead = self::fetchLeadById($lead->amo_id);

        // Log::info(__METHOD__, [$mortgageLead]); //DELETE

        self::$AMO_API->createTask( //FIXME: subject to refactoring
            (int) $mortgageLead['responsible_user_id'],
            (int) $mortgageLead['id'],
            time() + 10800,
            'Клиент забронировал КВ. Созвонись с клиентом и приступи к открытию Ипотеки',
            self::$TASK_TYPE_CONTROLL_ID
        );
        self::$AMO_API->updateLead([[
            "id"        => (int) $mortgageLead['id'],
            "status_id" => self::getMortgageApplyingStageId($lead),
        ]]);
    }
    public static function processMortgageLeadAfterApplication(Lead $lead)
    {
        Log::info(__METHOD__); //DELETE

        $mortgageLead = self::fetchLeadById($lead->amo_id);

        // Log::info(__METHOD__, [$mortgageLead]); //DELETE

        self::$AMO_API->createTask( //FIXME: subject to refactoring
            (int) $mortgageLead['responsible_user_id'],
            (int) $mortgageLead['id'],
            time() + 10800,
            'Клиент забронировал КВ. Созвонись с клиентом и приступи к открытию Ипотеки',
            self::$TASK_TYPE_CONTROLL_ID
        );
    }
    public static function processBasicLead(Lead $lead)
    {
        Log::info(__METHOD__); //DELETE

        if ($lead->amo_status_id === self::$STAGE_LOSS_ID) {
            return self::processLostBasicLead($lead);
        }

        if (self::isBasicLeadBooked($lead)) {
            return self::processBookedBasicLead($lead);
        }
    }
    public static function processLostBasicLead(Lead $lead)
    {
        Log::info(__METHOD__); //DELETE

        $mortgageLead = self::fetchLeadById($lead->lead->amo_id);

        self::$AMO_API->createTask(
            (int) $mortgageLead['responsible_user_id'],
            (int) $mortgageLead['id'],
            time() + 3600,
            'Сделка менеджера с клиентом в основной воронке перешла в "Закрыто не реализовано". Созвонись с клиентом. Если покупка не актуальна, то закрой все активные задачи. Если покупка актуальна, то свяжись с менеджером и выясни детали, а затем восстанови сделку.'
        );
        self::$AMO_API->updateLead([[
            "id"        => (int) $mortgageLead['id'],
            "status_id" => self::$STAGE_LOSS_ID,
        ]]);
    }
    public static function processBookedBasicLead(Lead $lead)
    {
        Log::info(__METHOD__); //DELETE

        if (self::isMortgageApplicationSubmited($lead->lead)) {
            return self::processMortgageLeadAfterApplication($lead->lead);
        }

        return self::processMortgageLeadBeforeApplication($lead->lead);
    }

    /* ACTIONS-METHODS */

    /* SCHEDULER-METHODS */
    public static function parseRecentWebhooks()
    {
        Log::info(__METHOD__); //DELETE

        self::initStatic();

        $leadWebhooks = self::getLeadWebhooks();

        foreach ($leadWebhooks as $leadWebhook) {
            Log::info(__METHOD__, [$leadWebhook->lead_id]); //DELETE

            $lead = Lead::getByAmoId((int) $leadWebhook->lead_id);

            if ($lead) {
                $lead->update([
                    'amo_status_id'   => self::getLeadWebhookStatusId($leadWebhook),
                    'amo_pipeline_id' => self::getLeadWebhookPipelineId($leadWebhook),
                ]);
                self::processWebhook($lead);
            }

            Log::info(__METHOD__, ['delete leadWebhook: ' . $leadWebhook->lead_id]); //DELETE

            $leadWebhook->delete();
        }
    }
}
