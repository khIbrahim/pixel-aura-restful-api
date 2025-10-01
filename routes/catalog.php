<?php

use App\Constants\V1\StoreTokenAbilities;
use App\Http\Controllers\V1\CatalogController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate'])
    ->group(function(){
        Route::prefix('catalog')->group(function(){
            Route::get('compact', [CatalogController::class, 'compact'])
                ->name('v1.catalog.compact')
                ->middleware('ability:' . StoreTokenAbilities::CATALOG_VIEW);

            Route::post('refresh', [CatalogController::class, 'refresh'])
                ->name('v1.catalog.refresh')
                ->middleware('ability:' . StoreTokenAbilities::CATALOG_VIEW);
        });
    });
