<?php

namespace App\Services\amoAPI;

use App\Exceptions\ForbiddenException;
use App\Services\amoAPI\amoHttp\amoClient;
use Illuminate\Support\Facades\Log;

class amoAPIHub
{
    private $client;
    private $pageItemLimit;
    private $amoData = [
        'client_id'     => null,
        'client_secret' => null,
        'code'          => null,
        'redirect_uri'  => null,
        'subdomain'     => null,
    ];

    public function __construct($amoData)
    {
        //echo 'const amoCRM<br>';

        $this->client = new amoClient();

        $this->pageItemLimit = 250;

        $this->amoData['client_id']     = $amoData['client_id'] ?? null;
        $this->amoData['client_secret'] = $amoData['client_secret'] ?? null;
        $this->amoData['code']          = $amoData['code'] ?? null;
        $this->amoData['redirect_uri']  = $amoData['redirect_uri'] ?? null;
        $this->amoData['subdomain']     = $amoData['subdomain'] ?? null;
        $this->amoData['access_token']  = $amoData['access_token'] ?? null;
    }

    public function auth()
    {
        $response = $this->client->sendRequest(
            [
                'url'     => 'https://' . $this->amoData['subdomain'] . '.amocrm.ru/oauth2/access_token',
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'method'  => 'POST',
                'data'    => [
                    'grant_type'    => 'authorization_code',
                    'client_id'     => $this->amoData['client_id'],
                    'client_secret' => $this->amoData['client_secret'],
                    'code'          => $this->amoData['code'],
                    'redirect_uri'  => $this->amoData['redirect_uri'],
                ],
            ]
        );

        if (
            $response['code'] < 200 ||
            $response['code'] > 204
        ) {
            throw new ForbiddenException("Access denied: " . $response['code']);
        }

        return $response['body'];
    }

    function list($entity) {
        if (!$entity) {
            return false;
        }

        $page       = 1;
        $entityList = [];
        $api        = '';

        switch ($entity) {
            case 'lead':
                $api = '/api/v4/leads';
                break;

            case 'contact':
                break;

            case 'users':
                $api = '/api/v4/users';
                break;

            default:
                break;
        }

        for (;; $page++) {
            //usleep( 500000 );

            $url = 'https://' . $this->amoData['subdomain'] . '.amocrm.ru' . $api . '?limit=' . $this->pageItemLimit . '&page=' . $page;

            $response = $this->client->sendRequest([
                'url'     => $url,
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->amoData['access_token'],
                ],
                'method'  => 'GET',
            ]);

            if ($response['code'] < 200 || $response['code'] >= 204) {
                break;
            }

            $entityList[$page - 1] = $response['body'];
        }

        return $entityList;
    }

    public function listByQuery($entity, $query)
    {
        if (!$entity) {
            return false;
        }

        $page       = 1;
        $entityList = [];
        $api        = '';

        switch ($entity) {
            case 'lead':
                $api = '/api/v4/leads';
                break;

            case 'contact':
                break;

            case 'users':
                $api = '/api/v4/users';
                break;

            case 'task':
                $api = '/api/v4/tasks';
                break;

            default:
                break;
        }

        for (;; $page++) {
            //usleep( 500000 );

            $url = 'https://' . $this->amoData['subdomain'] . '.amocrm.ru' . $api . '?limit=' . $this->pageItemLimit . '&page=' . $page . '&' . $query;

            $response = $this->client->sendRequest(

                [
                    'url'     => $url,
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . $this->amoData['access_token'],
                    ],
                    'method'  => 'GET',
                ]
            );

            if ($response['code'] < 200 || $response['code'] >= 204) {
                break;
            }

            $entityList[$page - 1] = $response['body'];
        }

        return $entityList;
    }

    public function findLeadById($id)
    {
        $url = "https://" . config('services.amoCRM.subdomain') . ".amocrm.ru/api/v4/leads/$id?with=contacts";

        try {
            $response = $this->client->sendRequest(

                [
                    'url'     => $url,
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . $this->amoData['access_token'],
                    ],
                    'method'  => 'GET',
                ]
            );

            if ($response['code'] < 200 || $response['code'] > 204) {
                throw new \Exception($response['code']);
            }

            return $response;
        } catch (\Exception$exception) {
            Log::error(
                __METHOD__,

                [
                    'message' => $exception->getMessage(),
                ]
            );

            return $response;
        }
    }

    public function findContactById($id)
    {
        $url = "https://" . config('services.amoCRM.subdomain') . ".amocrm.ru/api/v4/contacts/$id?with=leads";

        try {
            $response = $this->client->sendRequest(

                [
                    'url'     => $url,
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . $this->amoData['access_token'],
                    ],
                    'method'  => 'GET',
                ]
            );

            if ($response['code'] < 200 || $response['code'] > 204) {
                throw new \Exception($response['code']);
            }

            return $response;
        } catch (\Exception$exception) {
            Log::error(
                __METHOD__,

                [
                    'message' => $exception->getMessage(),
                ]
            );

            return $response;
        }
    }

    // FIXME das ist ein schlechte Beispiel- Man muss es nie wieder machen.
    public function copyLead($id, $responsible_user_id = false, $flag = false)
    {
        //echo 'copyLead<br>';
        $lead        = $this->findLeadById($id);
        $pipeline_id = (int) $lead['body']['pipeline_id'];

        Log::info(
            __METHOD__,

            [
                'message: copyLead << pipeline_id ' => ['pipeline_id' => $pipeline_id],
            ]
        );

        $pipelineGub      = 1393867;
        $pipelineGubPark  = 4551384;
        $pipelineDost     = 3302563;
        $pipelineDostPark = 4703964;

        if (!$responsible_user_id) {
            switch ($pipeline_id) {
                case $pipelineGub:
                case $pipelineGubPark:
                    $responsible_user_id = 7507200;
                    break;

                case $pipelineDost:
                case $pipelineDostPark:
                    $responsible_user_id = 7896546;
                    break;

                default:
                    $responsible_user_id = (int) config('app.amoCRM.mortgage_responsible_user_id');
                    break;
            }
        }

        //FIXME /////////////////////////////////////////////////////////
        $contacts = $lead['body']['_embedded']['contacts'];

        $newLeadContacts = [];

        for ($i = 0; $i < count($contacts); $i++) {
            $newLeadContacts[] = [
                "to_entity_id"   => $contacts[$i]['id'],
                "to_entity_type" => "contacts",
                "metadata"       => [
                    "is_main" => $contacts[$i]['is_main'] ? true : false,
                ],
            ];
        }

        //FIXME /////////////////////////////////////////////////////////

        //FIXME /////////////////////////////////////////////////////////
        $customFields        = $lead['body']['custom_fields_values'];
        $newLeadCustomFields = $this->parseCustomFields($customFields);

        $broker = $this->fetchUser($lead['body']['responsible_user_id']);

        if (
            $broker['code'] === 404 ||
            $broker['code'] === 400
        ) {
            return response(
                ['An error occurred in the server request while searching for a responsible user'],
                $broker['code']
            );
        } else if ($broker['code'] === 204) {
            return response(['Responsible user not found'], 404);
        }

        $newLeadCustomFields[] = [
            'field_id' => 757294,
            'values'   => [
                [
                    'value' => $broker['body']['name'],
                ],
            ],
        ];
        $newLeadCustomFields[] = [
            'field_id' => 757336,
            'values'   => [[
                'value' => time(),
            ]],
        ];
        //FIXME /////////////////////////////////////////////////////////

        $status_id = (int) config('app.amoCRM.mortgage_first_stage_id');

        if ($flag) {
            $status_id = 43332207;
        }

        try {
            $url = "https://" . config('services.amoCRM.subdomain') . ".amocrm.ru/api/v4/leads";

            $newLead = $this->client->sendRequest(
                [
                    'url'     => $url,
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . $this->amoData['access_token'],
                    ],
                    'method'  => 'POST',
                    'data'    => [
                        [
                            'name'                 => "Ипотека " . $lead['body']['name'],
                            'created_by'           => 0,
                            'price'                => $lead['body']['price'],
                            'responsible_user_id'  => $responsible_user_id,
                            'status_id'            => $status_id,
                            'pipeline_id'          => (int) config('app.amoCRM.mortgage_pipeline_id'),
                            'custom_fields_values' => $newLeadCustomFields,
                        ],
                    ],
                ]
            );

            if ($newLead['code'] < 200 || $newLead['code'] > 204) {
                throw new \Exception($newLead['code']);
            }

            $newLeadId = $newLead['body']['_embedded']['leads'][0]['id'];

            ////////////////////////////////////////////////////////////////////////////

            $url = "https://" . config('services.amoCRM.subdomain') . ".amocrm.ru/api/v4/leads/$newLeadId/link";

            $response = $this->client->sendRequest(
                [
                    'url'     => $url,
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . $this->amoData['access_token'],
                    ],
                    'method'  => 'POST',
                    'data'    => $newLeadContacts,

                ]
            );

            if ($response['code'] < 200 || $response['code'] > 204) {
                throw new \Exception($response['code']);
            }

            return $newLeadId;
        } catch (\Exception$exception) {
            Log::error(
                __METHOD__,

                [
                    'message' => $exception->getMessage(),
                ]
            );

            return false;
        }
    }

    public function parseCustomFields($cf)
    {
        $parsedCustomFields = [];

        if (!$cf) {
            return $parsedCustomFields;
        }

        for ($i = 0; $i < count($cf); $i++) {
            $tmp   = $cf[$i];
            $tmpCf = false;

            switch ($tmp['field_type']) {
                case 'text':
                case 'textarea':
                case 'numeric':
                case 'textarea':
                case 'price':
                case 'streetaddress':
                case 'tracking_data':
                case 'checkbox':
                case 'url':
                case 'date':
                case 'date_time':
                case 'birthday':
                    $tmpCf = [
                        'field_id' => (int) $tmp['field_id'],
                        'values'   => [
                            [
                                'value' => $tmp['values'][0]['value'],
                            ],
                        ],
                    ];
                    break;

                case 'select':
                case 'radiobutton':
                    $tmpCf = [
                        'field_id' => (int) $tmp['field_id'],
                        'values'   => [
                            [
                                'enum_id' => $tmp['values'][0]['enum_id'],
                            ],
                        ],
                    ];
                    break;

                default:
                    $tmpCf = false;
                    break;
            }

            if ($tmpCf) {
                $parsedCustomFields[] = $tmpCf;
            }
        }

        return $parsedCustomFields;
    }

    public function createTask($responsible_user_id, $entity_id, $complete_till, $text)
    {
        $url = "https://" . config('services.amoCRM.subdomain') . ".amocrm.ru/api/v4/tasks";

        try {
            $response = $this->client->sendRequest(
                [
                    'url'     => $url,
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . $this->amoData['access_token'],
                    ],
                    'method'  => 'POST',
                    'data'    => [
                        [
                            "task_type_id"        => 1,
                            'responsible_user_id' => $responsible_user_id,
                            "text"                => $text,
                            "complete_till"       => $complete_till,
                            "entity_id"           => $entity_id,
                            "entity_type"         => "leads",
                        ],
                    ],
                ]
            );

            if ($response['code'] < 200 || $response['code'] > 204) {
                throw new \Exception($response['code']);
            }

            return $response;
        } catch (\Exception$exception) {
            Log::error(
                __METHOD__,

                [
                    'message' => $exception->getMessage(),
                ]
            );

            return $response;
        }
    }

    public function updateLead($data)
    {
        $url = "https://" . config('services.amoCRM.subdomain') . ".amocrm.ru/api/v4/leads";

        try {
            $response = $this->client->sendRequest(
                [
                    'url'     => $url,
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . $this->amoData['access_token'],
                    ],
                    'method'  => 'PATCH',
                    'data'    => $data,
                ]
            );

            if ($response['code'] < 200 || $response['code'] > 204) {
                throw new \Exception($response['code']);
            }

            return $response;
        } catch (\Exception$exception) {
            Log::error(
                __METHOD__,

                [
                    'message' => $exception->getMessage(),
                ]
            );

            return $response;
        }
    }

    public function addTag($id, $tag)
    {
        $lead       = $this->findLeadById($id);
        $tagsNative = $lead['body']['_embedded']['tags'];
        $tags       = [];

        for ($i = 0; $i < count($tagsNative); $i++) {
            $tags[] = [
                'id' => (int) $tagsNative[$i]['id'],
            ];
        }

        $tags[] = [
            'name' => $tag,
        ];

        $this->updateLead(
            [
                [
                    'id'        => (int) $id,
                    "_embedded" => [
                        "tags" => $tags,
                    ],
                ],
            ]
        );
    }

    public function addTextNote($entityType, $entityId, $text)
    {
        if (!$entityType || !$entityId) {
            return;
        }

        $url = "https://" . config('services.amoCRM.subdomain') . ".amocrm.ru/api/v4/$entityType/$entityId/notes";

        try {
            $response = $this->client->sendRequest([
                'url'     => $url,
                'method'  => 'POST',
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->amoData['access_token'],
                ],
                'data'    => [[
                    "entity_id" => (int) $entityId,
                    "note_type" => "common",
                    "params"    => [
                        "text" => "Информация для брокера: " . $text,
                    ],
                ]],
            ]);

            if ($response['code'] < 200 || $response['code'] > 204) {
                throw new \Exception($response['code']);
            }

            return $response;
        } catch (\Exception$exception) {
            Log::error(__METHOD__, [
                'message' => $exception->getMessage(),
            ]);

            return $response;
        }
    }

    public function fetchUser($id = null)
    {
        if (!$id) {
            return false;
        }

        $url = "https://" . config('services.amoCRM.subdomain') . ".amocrm.ru/api/v4/users/$id";

        try {
            $response = $this->client->sendRequest([
                'url'     => $url,
                'method'  => 'GET',
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->amoData['access_token'],
                ],
            ]);

            if ($response['code'] < 200 || $response['code'] > 204) {
                throw new \Exception($response['code']);
            }

            return $response;
        } catch (\Exception$exception) {
            Log::error(__METHOD__, [
                'message' => $exception->getMessage(),
            ]);

            return $response;
        }
    }

    public function fetchUsers()
    {
        $url = "https://" . config('services.amoCRM.subdomain') . ".amocrm.ru/api/v4/users";

        try {
            $response = $this->client->sendRequest([
                'url'     => $url,
                'method'  => 'GET',
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->amoData['access_token'],
                ],
            ]);

            if ($response['code'] < 200 || $response['code'] > 204) {
                throw new \Exception($response['code']);
            }

            return $response;
        } catch (\Exception$exception) {
            Log::error(__METHOD__, [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function fetchPipelines()
    {
        $url = "https://" . config('services.amoCRM.subdomain') . ".amocrm.ru/api/v4/leads/pipelines";

        try {
            $response = $this->client->sendRequest([
                'url'     => $url,
                'method'  => 'GET',
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->amoData['access_token'],
                ],
            ]);

            if ($response['code'] < 200 || $response['code'] > 204) {
                throw new \Exception($response['code']);
            }

            return $response;
        } catch (\Exception$exception) {
            Log::error(__METHOD__, [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
