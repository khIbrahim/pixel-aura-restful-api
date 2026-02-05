<?php

use App\Http\Controllers\V1\DiscountsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/discounts')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate', 'store_member'])
    ->group(function () {
        Route::post('', [DiscountsController::class, 'store']);
//            ->middleware(['ability:' . \App\Constants\V1\StoreTokenAbilities::DISCOUNT_CREATE]);
    });
