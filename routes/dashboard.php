<?php

use App\Http\Controllers\V1\DashboardStatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/dashboard')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate'])
    ->group(function(){
        Route::get('/stats', [DashboardStatsController::class, 'stats'])
            ->name('dashboard.stats.index');
    });
