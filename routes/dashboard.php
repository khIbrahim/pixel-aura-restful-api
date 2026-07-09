<?php

use Illuminate\Support\Facades\Route;

Route::prefix('daashboard')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate'])
    ->group(function(){
        Route::get('/stats', [DashboardStatsController::class, 'index'])
            ->name('dashboard.stats.index');
    });
