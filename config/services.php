<?php

return [
    'mailgun'  => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses'      => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'amoCRM'   => [
        'client_secret'                  => env('AMOCRM_CLIENT_SECRET', null),
        'redirect_uri'                   => env('AMOCRM_REDIRECT_URI', null),
        'subdomain'                      => env('AMOCRM_SUBDOMAIN', null),
        'successful_stage_id'            => env('AMOCRM_SUCCESSFUL_STAGE_ID', null),
        'loss_stage_id'                  => env('AMOCRM_LOSS_STAGE_ID', null),
        'exclude_cf_utm_source_id'       => env('AMOCRM_EXCLUDE_CF_UTM_SOURCE_ID', null),
        'exclude_cf_utm_medium_id'       => env('AMOCRM_EXCLUDE_CF_UTM_MEDIUM_ID', null),
        'exclude_cf_utm_campaign_id'     => env('AMOCRM_EXCLUDE_CF_UTM_CAMPAIGN_ID', null),
        'exclude_cf_utm_term_id'         => env('AMOCRM_EXCLUDE_CF_UTM_TERM_ID', null),
        'exclude_cf_utm_content_id'      => env('AMOCRM_EXCLUDE_CF_UTM_CONTENT_ID', null),
        'exclude_cf_roistat_id'          => env('AMOCRM_EXCLUDE_CF_ROISTAT_ID', null),
        'exclude_cf_roistat_marker_id'   => env('AMOCRM_EXCLUDE_CF_ROISTAT_MARKER_ID', null),
        'exclude_cf_source_id'           => env('AMOCRM_EXCLUDE_CF_SOURCE_ID', null),
        'exclude_cf_mortgage_created_id' => env('AMOCRM_EXCLUDE_CF_MORTGAGE_CREATED_ID', null),
        'exclude_cf_broker_selected_id'  => env('AMOCRM_EXCLUDE_CF_BROKER_SELECTED_ID', null),
        'exclude_cf_lead_manager_id'     => env('AMOCRM_EXCLUDE_CF_LEAD_MANAGER_ID', null),
        'exclude_cf_rejection_reason_id' => env('AMOCRM_EXCLUDE_CF_REJECTION_REASON_ID', null),
    ],
];
