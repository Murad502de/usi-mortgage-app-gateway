<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Broker\BrokerCreateRequest;
use App\Http\Requests\Api\Broker\BrokerUpdateRequest;
use App\Http\Resources\Broker\BrokerResource;
use App\Http\Resources\Broker\BrokersResource;
use App\Models\Broker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BrokerController extends Controller
{
    public function index(Request $request)
    {
        return BrokersResource::collection(Broker::all());
    }
    public function create(BrokerCreateRequest $request)
    {
        $pipeline = Broker::createBroker(($request->all()));

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
