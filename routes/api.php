<?php

use App\Http\Controllers\Api\Dictionaries\LeadsPipelinesDictionaryController;
use App\Http\Controllers\Api\Dictionaries\LeadsUsersDictionaryController;
use App\Http\Controllers\Api\MortgageController;
use App\Http\Controllers\Api\PipelineController;
use App\Http\Controllers\Api\Services\AmoCrm\AmoCrmAuthController;
use App\Http\Controllers\Api\Webhooks\LeadWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ConfigController;

Route::prefix('v1')->group(function () {
    Route::prefix('webhooks')->group(function () {
        Route::prefix('leads')->group(function () {
            Route::post('create', [LeadWebhookController::class, 'create']);
            Route::post('update', [LeadWebhookController::class, 'update']);
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
    });

    Route::prefix('pipelines')->group(function () {
        Route::get('/', [PipelineController::class, 'index']);
        Route::post('/', [PipelineController::class, 'create']);
        Route::get('/{pipeline:uuid}', [PipelineController::class, 'get']);
        Route::put('/{pipeline:uuid}/update', [PipelineController::class, 'update']);
        Route::delete('/{pipeline:uuid}/delete', [PipelineController::class, 'delete']);
    });

    Route::prefix('broker')->group(function () {
        Route::get('/', [PipelineController::class, 'index']);
        Route::post('/', [PipelineController::class, 'create']);
        Route::get('/{pipeline:uuid}', [PipelineController::class, 'get']);
        Route::put('/{pipeline:uuid}/update', [PipelineController::class, 'update']);
        Route::delete('/{pipeline:uuid}/delete', [PipelineController::class, 'delete']);
    });
});
