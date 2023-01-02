<?php

use App\Http\Controllers\Api\BrokerController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\Dictionaries\LeadsPipelinesDictionaryController;
use App\Http\Controllers\Api\Dictionaries\LeadsUsersDictionaryController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\MortgageController;
use App\Http\Controllers\Api\PipelineController;
use App\Http\Controllers\Api\Services\AmoCrm\AmoCrmAuthController;
use App\Http\Controllers\Api\Webhooks\LeadWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('webhooks')->group(function () {
        Route::prefix('leads')->group(function () {
            Route::post('change-stage', [LeadWebhookController::class, 'changeStage']);
        });
    });

    Route::prefix('services')->group(function () {
        Route::prefix('amocrm')->group(function () {
            Route::prefix('auth')->group(function () {
                Route::get('signin', [AmoCrmAuthController::class, 'signin']);
                Route::get('signout', [AmoCrmAuthController::class, 'signout']);
            });
        });
    });

    Route::prefix('dictionaries')->group(function () {
        Route::get('users', [LeadsUsersDictionaryController::class, 'users']);
        Route::prefix('leads')->group(function () {
            Route::get('pipelines', [LeadsPipelinesDictionaryController::class, 'pipelines']);
        });
    });

    Route::prefix('config')->group(function () {
        Route::get('/leadcard', [ConfigController::class, 'leadcard']);
    });

    Route::prefix('mortgages')->group(function () {
        Route::get('/', [MortgageController::class, 'index']);
        Route::post('/', [MortgageController::class, 'create']);
        Route::get('/{mortgage:uuid}', [MortgageController::class, 'get']);
        Route::put('/{mortgage:uuid}/update', [MortgageController::class, 'update']);
        Route::delete('/{mortgage:uuid}/delete', [MortgageController::class, 'delete']);
        Route::get('id/{mortgage:amo_mortgage_id}', [MortgageController::class, 'get']);
    });

    Route::prefix('pipelines')->group(function () {
        Route::get('/', [PipelineController::class, 'index']);
        Route::post('/', [PipelineController::class, 'create']);
        Route::get('/{pipeline:uuid}', [PipelineController::class, 'get']);
        Route::put('/{pipeline:uuid}/update', [PipelineController::class, 'update']);
        Route::delete('/{pipeline:uuid}/delete', [PipelineController::class, 'delete']);
        Route::get('id/{pipeline:amo_pipeline_id}', [PipelineController::class, 'get']);
    });

    Route::prefix('brokers')->group(function () {
        Route::get('/', [BrokerController::class, 'index']);
        Route::post('/', [BrokerController::class, 'create']);
        Route::get('/{broker:uuid}', [BrokerController::class, 'get']);
        Route::put('/{broker:uuid}/update', [BrokerController::class, 'update']);
        Route::delete('/{broker:uuid}/delete', [BrokerController::class, 'delete']);
    });

    Route::prefix('leads')->middleware('auth.amocrm.token')->group(function () {
        Route::get('/', [LeadController::class, 'index']);
        Route::post('/', [LeadController::class, 'create']);
        Route::get('/{lead:amo_id}', [LeadController::class, 'get']);
        Route::put('/{lead:amo_id}/update', [LeadController::class, 'update']);
        Route::delete('/{lead:amo_id}/delete', [LeadController::class, 'delete']);
    });
});
