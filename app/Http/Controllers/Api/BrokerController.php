<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Broker;

class BrokerController extends Controller
{
    public function index(Request $request)
    {
        return BrokersResource::collection(Broker::all());
    }
    public function create(BrokerCreateRequest $request)
    {
        $pipeline = Broker::createdBroker(($request->all()));

        return $pipeline ? $pipeline->uuid : null;
    }
    public function get(Broker $pipeline): BrokerResource
    {
        return new BrokerResource($pipeline);
    }
    public function update(Broker $pipeline, BrokerUpdateRequest $request)
    {
        $pipeline->updateBroker($request->all());

        return response()->json(['message' => 'success by update'], Response::HTTP_OK);
    }
    public function delete(Broker $pipeline)
    {
        $pipeline->delete();

        return response()->json(['message' => 'success by delete'], Response::HTTP_OK);
    }
}
