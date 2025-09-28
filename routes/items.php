<?php

use App\Constants\V1\StoreTokenAbilities;
use App\Http\Controllers\V1\ItemsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware([
        'auth:sanctum',
        'device.ctx',
        'device.throttle:per-device',
        'correlate',
        'store_member',
    ])
    ->group(function () {
        Route::post('/items', [ItemsController::class, 'store'])
            ->name('items.store')
            ->middleware(['ability:'.StoreTokenAbilities::ITEM_CREATE]);

        Route::get('/items/{item}', [ItemsController::class, 'show'])
            ->name('items.show')
            ->middleware(['ability:'.StoreTokenAbilities::ITEM_READ]);

        Route::get('/items', [ItemsController::class, 'index'])
            ->name('items.index')
            ->middleware(['ability:'.StoreTokenAbilities::ITEM_READ]);

        Route::match(['put', 'patch'], '/items/{item}', [ItemsController::class, 'update'])
            ->name('items.update')
            ->middleware(['ability:'.StoreTokenAbilities::ITEM_UPDATE]);

        Route::delete('/items/{item}', [ItemsController::class, 'destroy'])
            ->name('items.destroy')
            ->middleware(['ability:'.StoreTokenAbilities::ITEM_DELETE]);
    });
