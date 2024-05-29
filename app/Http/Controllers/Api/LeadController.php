<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Lead\LeadCreateRequest;
use App\Http\Requests\Api\Lead\LeadUpdateRequest;
use App\Http\Resources\Lead\LeadResource;
use App\Http\Resources\Lead\LeadsResource;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        return LeadsResource::collection(Lead::all());
    }
    public function create(LeadCreateRequest $request)
    {
        $leadId = Lead::createLead($request->all());

        if (!$leadId) {
            return response()->json(['message' => 'failed by create'], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(['message' => 'success by create', 'lead' => $leadId], Response::HTTP_OK);

        // return response()->json(['message' => 'success by create'], Response::HTTP_OK); //DELETE
    }
    public function get(Lead $lead): LeadResource
    {
        Log::info(__METHOD__, [$lead]); //DELETE

        return new LeadResource($lead);
    }
    public function update(Lead $lead, LeadUpdateRequest $request)
    {
        $lead->update($request->all());

        return response()->json(['message' => 'success by update'], Response::HTTP_OK);
    }
    public function delete(Lead $lead)
    {
        $lead->delete();

        return response()->json(['message' => 'success by delete'], Response::HTTP_OK);
    }
}
