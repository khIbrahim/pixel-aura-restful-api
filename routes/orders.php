<?php

use App\Http\Controllers\V1\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/orders')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate'])
    ->group(function(){
        Route::post('/', [OrderController::class, 'store'])
            ->name('orders.store');

        Route::get('/{order}', [OrderController::class, 'show'])
            ->name('orders.show');
    });
