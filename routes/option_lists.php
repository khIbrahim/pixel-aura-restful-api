<?php

use App\Constants\V1\StoreTokenAbilities;
use App\Http\Controllers\V1\OptionListController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/option-lists')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate', 'store_member'])
    ->group(function() {
        Route::get('/', [OptionListController::class, 'index'])
            ->name('option-lists.index')
            ->middleware(['ability:' . StoreTokenAbilities::OPTION_LIST_READ]);

        Route::post('/', [OptionListController::class, 'store'])
            ->name('option-lists.store')
            ->middleware(['ability:' . StoreTokenAbilities::OPTION_LIST_CREATE]);

        Route::get('/{optionList}', [OptionListController::class, 'show'])
            ->name('option-lists.show')
            ->middleware(['ability:' . StoreTokenAbilities::OPTION_LIST_READ]);

        Route::put('/{optionList}', [OptionListController::class, 'update'])
            ->name('option-lists.update')
            ->middleware(['ability:' . StoreTokenAbilities::OPTION_LIST_UPDATE]);

        Route::delete('/{optionList}', [OptionListController::class, 'destroy'])
            ->name('option-lists.destroy')
            ->middleware(['ability:' . StoreTokenAbilities::OPTION_LIST_DELETE]);
    });
