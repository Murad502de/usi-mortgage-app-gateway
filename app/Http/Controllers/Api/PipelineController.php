<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Pipeline\PipelineCreateRequest;
use App\Http\Requests\Api\Pipeline\PipelineUpdateRequest;
use App\Http\Resources\Pipeline\PipelineResource;
use App\Http\Resources\Pipeline\PipelinesResource;
use App\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PipelineController extends Controller
{
    public function index(Request $request)
    {
        return PipelinesResource::collection(Pipeline::all());
    }
    public function create(PipelineCreateRequest $request)
    {
        $pipeline = Pipeline::createdPipeline(($request->all()));

        return $pipeline ? $pipeline->uuid : null;
    }
    public function get(Pipeline $pipeline): PipelineResource
    {
        return new PipelineResource($pipeline);
    }
    public function update(Pipeline $pipeline, PipelineUpdateRequest $request)
    {
        $pipeline->update($request->all());

        return response()->json(['message' => 'success by update'], Response::HTTP_OK);
    }
    public function delete(Pipeline $pipeline)
    {
        $pipeline->delete();

        return response()->json(['message' => 'success by delete'], Response::HTTP_OK);
    }
}
