<?php

use App\Constants\V1\StoreTokenAbilities;
use App\Contracts\V1\ItemVariant\ItemVariantRepositoryInterface;
use App\Http\Controllers\V1\ItemVariantController;
use Illuminate\Support\Facades\Route;

Route::bind('itemVariant', function ($value) {
    $repository = app(ItemVariantRepositoryInterface::class);

    return is_numeric($value)
        ? $repository->findOrFail($value)
        : ($repository->findBy('sku', $value) ?? abort(404, 'No query results for model [App\\Models\\V1\\ItemVariant]'));
});

Route::prefix('v1/items/{item}/variants')
    ->middleware([
        'auth:sanctum',
        'device.ctx',
        'device.throttle:per-device',
        'correlate',
        'store_member',
    ])
    ->group(function () {
        Route::get('/', [ItemVariantController::class, 'index'])
            ->name('item_variants.index')
            ->middleware(['ability:'.StoreTokenAbilities::ITEM_READ]);

        Route::post('/', [ItemVariantController::class, 'store'])
            ->name('item_variants.store')
            ->middleware(['ability:'.StoreTokenAbilities::ITEM_UPDATE]);

        Route::get('/{itemVariant}', [ItemVariantController::class, 'show'])
            ->name('item_variants.show')
            ->middleware(['ability:'.StoreTokenAbilities::ITEM_READ]);

        Route::put('/{itemVariant}', [ItemVariantController::class, 'update'])
            ->name('item_variants.update')
            ->middleware(['ability:'.StoreTokenAbilities::ITEM_UPDATE]);

        Route::delete('/{itemVariant}', [ItemVariantController::class, 'destroy'])
            ->name('item_variants.destroy')
            ->middleware(['ability:'.StoreTokenAbilities::ITEM_UPDATE]);

        // toggle active
        Route::post('/{itemVariant}/toggle-active', [ItemVariantController::class, 'toggleActive'])
            ->name('item_variants.toggle_active')
            ->middleware(['ability:'.StoreTokenAbilities::ITEM_UPDATE]);
    });
