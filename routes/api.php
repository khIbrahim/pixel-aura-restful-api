<?php

use App\Http\Controllers\V1\MeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate'])
    ->group(function () {
        Route::get('/me', [MeController::class, 'me']);
    });
