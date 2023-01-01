<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Webhooks\LeadWebhookAddRequest;
use App\Http\Requests\Api\Webhooks\LeadWebhookChangeStageRequest;
use App\Http\Requests\Api\Webhooks\LeadWebhookUpdateRequest;
use App\Models\Webhooks\ChangeStageWebhook;
use Illuminate\Http\Response;

class LeadWebhookController extends Controller
{
    public function create(LeadWebhookAddRequest $request)
    {
        $this->handle($request->all()['leads']['add'][0]);

        return response()->json(['message' => 'success by create'], Response::HTTP_OK);
    }
    public function update(LeadWebhookUpdateRequest $request)
    {
        $this->handle($request->all()['leads']['update'][0]);

        return response()->json(['message' => 'success by update'], Response::HTTP_OK);
    }
    public function changeStage(LeadWebhookChangeStageRequest $request)
    {
        $this->handle($request->all()['leads']['status'][0]);

        return response()->json(['message' => 'success by changeStage'], Response::HTTP_OK);
    }

    private function handle(array $data)
    {
        if (isset($data['id'])) {
            $lead = ChangeStageWebhook::getLeadByAmoId($data['id']);

            if ($lead) {
                if ($lead->last_modified < (int) $data['last_modified']) {
                    ChangeStageWebhook::updateLead($data['id'], $data['last_modified'], $data);
                }
            } else {
                ChangeStageWebhook::createLead($data['id'], $data['last_modified'], $data);
            }
        }
    }
}
