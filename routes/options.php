<?php

use App\Constants\V1\StoreTokenAbilities;
use App\Http\Controllers\V1\OptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/options')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate'])
    ->group(function () {
        Route::post('/', [OptionController::class, 'store'])
            ->name('options.store')
            ->middleware(['store_member', 'ability:' . StoreTokenAbilities::OPTION_CREATE]);

        Route::get('/', [OptionController::class, 'index'])
            ->name('options.index')
            ->middleware('ability:' . StoreTokenAbilities::OPTION_READ);

        Route::get('/{option}', [OptionController::class, 'show'])
            ->name('options.show')
            ->middleware('ability:' . StoreTokenAbilities::OPTION_READ);

        Route::match(['put', 'patch'], '/{option}', [OptionController::class, 'update'])
            ->middleware(['store_member', 'ability:' . StoreTokenAbilities::UPDATE_OPTION]);

        Route::delete('/{option}', [OptionController::class, 'destroy'])
            ->name('options.destroy')
            ->middleware(['store_member', 'ability:' . StoreTokenAbilities::OPTION_DELETE]);
    });
