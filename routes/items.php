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
        'store_member'
    ])
    ->group(function () {
        Route::post('/items', [ItemsController::class, 'store'])
            ->name('items.store')
            ->middleware(['ability:' . StoreTokenAbilities::ITEM_CREATE]);

        Route::get('/items/{item}', [ItemsController::class, 'show'])
            ->name('items.show')
            ->middleware(['ability:' . StoreTokenAbilities::ITEM_READ]);

        Route::get('/items', [ItemsController::class, 'index'])
            ->name('items.index')
            ->middleware(['ability:' . StoreTokenAbilities::ITEM_READ]);

        Route::get('/items/{item}/ingredients', [ItemsController::class, 'listIngredients'])
            ->name('items.ingredients.index')
            ->middleware(['ability:' . StoreTokenAbilities::ITEM_READ]);

        Route::post('items/{item}/options', [ItemsController::class, 'attachOptions'])
            ->name('items.options.attach')
            ->middleware(['ability:' . StoreTokenAbilities::ITEM_UPDATE]);

        Route::delete('items/{item}/options/{option}', [ItemsController::class, 'detachOption'])
            ->name('items.options.detach')
            ->middleware(['ability:' . StoreTokenAbilities::ITEM_UPDATE]);

        Route::post('items/{item}/ingredients', [ItemsController::class, 'attachIngredients'])
            ->name('items.ingredients.attach')
            ->middleware(['ability:' . StoreTokenAbilities::ITEM_UPDATE]);

        Route::get('/items/{item}/options', [ItemsController::class, 'listOptions'])
            ->name('items.options.index')
            ->middleware(['ability:' . StoreTokenAbilities::ITEM_READ]);

        Route::get('/items/{item}/variants', [ItemsController::class, 'listVariants'])
            ->name('items.variants.index')
            ->middleware(['ability:' . StoreTokenAbilities::ITEM_READ]);

        Route::match(['put', 'patch'], '/items/{item}', [ItemsController::class, 'update'])
            ->name('items.update')
            ->middleware(['ability:' . StoreTokenAbilities::ITEM_UPDATE]);

        Route::delete('items/{item}/ingredients/{ingredient}', [ItemsController::class, 'detachIngredient'])
            ->name('items.ingredients.detach')
            ->middleware(['ability:' . StoreTokenAbilities::ITEM_UPDATE]);

        Route::delete('items/{item}/ingredients', [ItemsController::class, 'detachIngredients'])
            ->name('items.ingredients.detach.multiple')
            ->middleware(['ability:' . StoreTokenAbilities::ITEM_UPDATE]);
    });
