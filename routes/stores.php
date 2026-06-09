<?php

use App\Http\Controllers\V1\StoresController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['device.throttle:per-device', 'correlate'])
    ->group(function () {
        Route::apiResource('stores', StoresController::class)
            ->parameters(['stores' => 'store'])
            ->whereNumber('store');
    });
