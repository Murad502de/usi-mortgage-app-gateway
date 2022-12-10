<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mortgage\MortgageCreateRequest;
use App\Http\Requests\Api\Mortgage\MortgageUpdateRequest;
use App\Http\Resources\Mortgage\MortgageResource;
use App\Http\Resources\Mortgage\MortgagesResource;
use App\Models\Mortgage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MortgageController extends Controller
{
    public function index(Request $request)
    {
        Log::info(__METHOD__); //DELETE

        return MortgagesResource::collection(Mortgage::all());
    }
    public function create(MortgageCreateRequest $request)
    {
        Log::info(__METHOD__, $request->all()); //DELETE

        Mortgage::createMortgage($request->all());

        return true;
    }
    public function get(Mortgage $mortgage): MortgageResource
    {
        Log::info(__METHOD__); //DELETE

        return new MortgageResource($mortgage);
    }
    public function update(Mortgage $mortgage, MortgageUpdateRequest $request)
    {
        Log::info(__METHOD__); //DELETE

        return true;
    }
    public function delete(Mortgage $mortgage)
    {
        Log::info(__METHOD__); //DELETE

        return true;
    }
}
