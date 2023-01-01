<?php

namespace App\Models\Webhooks;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    private const PARSE_COUNT = 20;

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

    /* FETCH-METHODS */

    /* PARSE-METHODS */

    /* FUNCTIONS-METHODS */

    /* FILTER-METHODS */

    /* PROCEDURES-METHODS */
    public static function processWebhook(ChangeStageWebhook $leadWebhook, Lead $lead)
    {
        Log::info(__METHOD__); //DELETE

        if (!!$lead->is_mortgage) {
            return self::processMortgageLead($leadWebhook, $lead);
        }

        return self::processBasicLead($leadWebhook, $lead);
    }
    public static function processMortgageLead(ChangeStageWebhook $leadWebhook, Lead $lead): void
    {
        Log::info(__METHOD__); //DELETE
    }
    public static function processBasicLead(ChangeStageWebhook $leadWebhook, Lead $lead): void
    {
        Log::info(__METHOD__); //DELETE
    }

    /* ACTIONS-METHODS */

    /* SCHEDULER-METHODS */
    public static function parseRecentWebhooks()
    {
        Log::info(__METHOD__); //DELETE

        $leadWebhooks = self::getLeadWebhooks();

        foreach ($leadWebhooks as $leadWebhook) {
            Log::info(__METHOD__, [$leadWebhook->lead_id]); //DELETE

            $lead = Lead::getByAmoId((int) $leadWebhook->lead_id);

            if ($lead) {
                self::processWebhook($leadWebhook, $lead);
            }

            // $lead->delete(); //TODO
        }
    }
}
