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

class LeadController extends Controller
{
    public function index(Request $request)
    {
        return LeadsResource::collection(Lead::all());
    }
    public function create(LeadCreateRequest $request)
    {
        $mortgage = Lead::createLead($request->all());

        return $mortgage ? $mortgage->uuid : null;
    }
    public function get(Lead $mortgage): LeadResource
    {
        return new LeadResource($mortgage);
    }
    public function update(Lead $mortgage, LeadUpdateRequest $request)
    {
        $mortgage->update($request->all());

        return response()->json(['message' => 'success by update'], Response::HTTP_OK);
    }
    public function delete(Lead $mortgage)
    {
        $mortgage->delete();

        return response()->json(['message' => 'success by delete'], Response::HTTP_OK);
    }
}
