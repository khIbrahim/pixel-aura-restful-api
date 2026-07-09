<?php

use App\Http\Controllers\V1\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/notifications')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate'])
    ->group(function() {
        Route::get('/', [NotificationController::class, 'index'])
            ->name('notifications.index');

        Route::patch('/read-all', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.read_all');

        Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');

        Route::delete('/{notification}', [NotificationController::class, 'destroy'])
            ->name('notifications.destroy');
    });
