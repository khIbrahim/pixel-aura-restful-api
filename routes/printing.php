<?php

use App\Http\Controllers\V1\PrintingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/printing')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate', 'store_member'])
    ->group(function(){
        Route::get('/printers', [PrintingController::class, 'printers'])
            ->name('printing.printers.index');

        Route::get('/settings', [PrintingController::class, 'settings'])
            ->name('printing.settings.show');

        Route::put('/settings', [PrintingController::class, 'updateSettings'])
            ->name('printing.settings.update');

        Route::post('/orders/{order}/print', [PrintingController::class, 'printOrder'])
            ->name('printing.orders.print');
    });
