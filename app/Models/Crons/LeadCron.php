<?php

namespace App\Models\Crons;

use App\Jobs\CalculatePriceWithDiscount;
use App\Models\Lead;
use App\Services\amoAPI\Entities\Lead as AmoLead;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Illuminate\Support\Facades\Log;

class LeadCron extends Model
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

    public static function createLead(string $leadId, int $lastModified, array $data): void
    {
        self::create([
            'lead_id'       => $leadId,
            'last_modified' => (int) $lastModified,
            'data'          => json_encode($data),
        ]);
    }
    public static function getLeadByAmoId(string $leadId): ?LeadCron
    {
        return self::all()->where('lead_id', $leadId)->first();
    }
    public static function updateLead(string $leadId, int $lastModified, array $data): void
    {
        self::where('lead_id', $leadId)->update([
            'last_modified' => (int) $lastModified,
            'data'          => json_encode($data),
        ]);
    }
    public static function parseRecentWebhooks()
    {
        $leads = self::orderBy('id', 'asc')
            ->take(self::PARSE_COUNT)
            ->get();

        foreach ($leads as $lead) {
            $LEAD = Lead::getLeadByAmoId((int) $lead->lead_id);

            $LEAD ? self::haveAvailabilityLead($lead, $LEAD) : self::dontHaveAvailabilityLead($lead);

            $lead->delete();
        }
    }

    /* PROCEDURES */
    private static function haveAvailabilityLead(LeadCron $lead, Lead $LEAD): void
    {
        $LEAD_DATA       = json_decode($lead->data, true);
        $CUSTOM_FIELDS   = isset($LEAD_DATA['custom_fields']) ? $LEAD_DATA['custom_fields'] : null;
        $OLD_STATUS      = (int) $LEAD->status_id;
        $OLD_CARD        = $LEAD->card ? $LEAD->card : null;
        $OLD_CARD_NUMBER = $OLD_CARD ? $OLD_CARD->number : '';
        $OLD_PRICE       = (int) $LEAD->price;
        $STATUS          = (int) $LEAD_DATA['status_id'];
        $CARD_NUMBER     = self::findDiscountCardValue($CUSTOM_FIELDS) ?? '';
        $PRICE           = (int) AmoLead::findCustomFieldById(
            $CUSTOM_FIELDS,
            config('services.amoCRM.price_without_discount_id')
        );

        // Log::info(__METHOD__, ['ID: ', $LEAD->amocrm_id]); //DELETE
        // Log::info(__METHOD__, ['PRICE - OLD_PRICE: ', $PRICE . ' - ' . $OLD_PRICE]); //DELETE
        // Log::info(__METHOD__, ['CARD_NUMBER - OLD_CARD: ', $CARD_NUMBER . ' - ' . $OLD_CARD_NUMBER]); //DELETE
        // Log::info(__METHOD__, ['STATUS - OLD_STATUS: ', $LEAD_DATA['status_id'] . ' - ' . $OLD_STATUS]); //DELETE

        if (
            (int) $LEAD->status_id !== (int) $LEAD_DATA['status_id'] ||
            self::isPriceChanged($PRICE, $OLD_PRICE) ||
            self::isCardChanged($CARD_NUMBER, $OLD_CARD_NUMBER)
        ) {
            // Log::info(__METHOD__, ['must update']); //DELETE

            $updateLead = Lead::updateLead( // TODO check
                (int) $LEAD->amocrm_id,
                (int) $LEAD_DATA['status_id'],
                $CARD_NUMBER,
                $PRICE,
            );

            if (
                !self::isLossStageNotChanged($STATUS, $OLD_STATUS) &&
                (
                    self::movedLeadFromNotLossToLoss($STATUS, $OLD_STATUS) ||
                    self::movedLeadFromLossToNotLoss($STATUS, $OLD_STATUS) ||
                    self::isPriceChanged($PRICE, $OLD_PRICE) ||
                    self::isCardChanged($CARD_NUMBER, $OLD_CARD_NUMBER)
                )
            ) {
                // Log::info(__METHOD__, ['set job: CalculatePriceWithDiscount']); //DELETE

                CalculatePriceWithDiscount::dispatch(
                    $updateLead,
                    $OLD_PRICE,
                    $OLD_STATUS,
                    $OLD_CARD
                );
            }
        }
    }
    private static function dontHaveAvailabilityLead(LeadCron $lead): void
    {
        // Log::info(__METHOD__); //DELETE

        $LEAD_DATA     = json_decode($lead->data, true);
        $CUSTOM_FIELDS = isset($LEAD_DATA['custom_fields']) ? $LEAD_DATA['custom_fields'] : null;
        $CARD_NUMBER   = self::findDiscountCardValue($CUSTOM_FIELDS);

        if ($CARD_NUMBER) {
            $newLead = Lead::createLead(
                (int) $lead->lead_id,
                (int) $LEAD_DATA['status_id'],
                $CARD_NUMBER,
                (int) AmoLead::findCustomFieldById(
                    $CUSTOM_FIELDS,
                    config('services.amoCRM.price_without_discount_id')
                ),
            );

            CalculatePriceWithDiscount::dispatch($newLead);
        }
    }

    /* FUNCTIONS */
    private static function findDiscountCardValue($customFields): ?string
    {
        return AmoLead::findCustomFieldById(
            $customFields,
            config('services.amoCRM.discount_card_field_id')
        );
    }
    private static function isPriceChanged(int $newPrice, int $oldPrice): bool
    {
        // Log::info(__METHOD__, [$newPrice !== $oldPrice]); //DELETE

        return $newPrice !== $oldPrice;
    }
    private static function isCardChanged($newCard, $oldCard): bool
    {
        // Log::info(__METHOD__, [!$oldCard && $newCard || ($oldCard && ($oldCard !== $newCard))]); //DELETE

        return !$oldCard && $newCard || ($oldCard && ($oldCard !== $newCard));
    }
    private static function movedLeadFromNotLossToLoss(int $newStatusId, int $oldStatusId): bool
    {
        // Log::info(__METHOD__, [$oldStatusId !== (int) config('services.amoCRM.loss_stage_id') && $newStatusId === (int) config('services.amoCRM.loss_stage_id')]); //DELETE

        return $oldStatusId !== (int) config('services.amoCRM.loss_stage_id') &&
        $newStatusId === (int) config('services.amoCRM.loss_stage_id');
    }
    private static function movedLeadFromLossToNotLoss(int $newStatusId, int $oldStatusId): bool
    {
        // Log::info(__METHOD__, [$oldStatusId === (int) config('services.amoCRM.loss_stage_id') && $newStatusId !== (int) config('services.amoCRM.loss_stage_id')]); //DELETE

        return $oldStatusId === (int) config('services.amoCRM.loss_stage_id') &&
        $newStatusId !== (int) config('services.amoCRM.loss_stage_id');
    }
    private static function isLossStageNotChanged(int $newStatusId, int $oldStatusId): bool
    {
        // Log::info(__METHOD__, [$oldStatusId === (int) config('services.amoCRM.loss_stage_id') && $newStatusId === (int) config('services.amoCRM.loss_stage_id')]); //DELETE

        return $oldStatusId === (int) config('services.amoCRM.loss_stage_id') && $newStatusId === (int) config('services.amoCRM.loss_stage_id');
    }
}
