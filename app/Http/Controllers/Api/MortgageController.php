<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mortgage\MortgageCreateRequest;
use App\Http\Requests\Api\Mortgage\MortgageUpdateRequest;
use App\Http\Resources\Mortgage\MortgageResource;
use App\Http\Resources\Mortgage\MortgagesResource;
use App\Models\Mortgage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MortgageController extends Controller
{
    public function index(Request $request)
    {
        return MortgagesResource::collection(Mortgage::all());
    }
    public function create(MortgageCreateRequest $request)
    {
        $mortgage = Mortgage::createMortgage($request->all());

        return $mortgage ? $mortgage->uuid : null;
    }
    public function get(Mortgage $mortgage): MortgageResource
    {
        return new MortgageResource($mortgage);
    }
    public function update(Mortgage $mortgage, MortgageUpdateRequest $request)
    {
        $mortgage->update($request->all());

        return response()->json(['message' => 'success by update'], Response::HTTP_OK);
    }
    public function delete(Mortgage $mortgage)
    {
        $mortgage->delete();

        return response()->json(['message' => 'success by delete'], Response::HTTP_OK);
    }
}
