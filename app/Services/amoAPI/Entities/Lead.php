<?php

namespace App\Services\amoAPI\Entities;

// use App\Services\amoAPI\amoHttp\amoClient;
// use Illuminate\Support\Facades\Log;
class Lead
{
    public function __construct()
    {}

    public static function findCustomFieldById($customFields, $customFieldId)
    {
        // Log::info(__METHOD__, [$customFieldId]); //DELETE
        // Log::info(__METHOD__, [$customFields]); //DELETE

        if (!$customFields) {
            return null;
        }

        foreach ($customFields as $customField) {
            if ((int) $customField['field_id'] === (int) $customFieldId) {
                return $customField['values'][0]['value'];
            }
        }

        return null;
    }
}
