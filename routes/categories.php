<?php

use App\Constants\V1\StoreTokenAbilities;
use App\Http\Controllers\V1\CategoriesController;
use App\Support\Facades\Ability;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/categories')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate', 'store_member'])
    ->group(function () {
        Route::get('/', [CategoriesController::class, 'index'])
            ->middleware(['ability:' . StoreTokenAbilities::CATEGORY_READ]);

        Route::post('/', [CategoriesController::class, 'store'])
            ->middleware(['ability:' . StoreTokenAbilities::CATEGORY_CREATE]);

        Route::get('{category}', [CategoriesController::class, 'show'])
            ->middleware(['ability:' . Ability::for("categories", "read")]);

        Route::match(['put', 'patch'], '{category}', [CategoriesController::class, 'update'])
            ->middleware(['ability:' . StoreTokenAbilities::CATEGORY_UPDATE]);

        Route::delete('{category}', [CategoriesController::class, 'destroy'])
            ->middleware(['ability:' . StoreTokenAbilities::CATEGORY_DELETE]);

        Route::patch('reorder', [CategoriesController::class, 'reorder'])
            ->middleware(['ability:' . StoreTokenAbilities::CATEGORY_REORDER]);

        Route::patch('{category}/activation', [CategoriesController::class, 'toggleActivation'])
            ->middleware(['ability:' . StoreTokenAbilities::CATEGORY_ACTIVATE]);

        /** Images */
        Route::prefix('{category}/images')->group(function () {
            Route::post('/', [CategoriesController::class, 'uploadImage'])
                ->middleware(['ability:' . StoreTokenAbilities::CATEGORY_UPDATE]);

            Route::delete('/{image?}', [CategoriesController::class, 'deleteImage'])
                ->middleware(['ability:' . StoreTokenAbilities::CATEGORY_UPDATE]);

            Route::get('/{image?}', [CategoriesController::class, 'listImages'])
                ->middleware(['ability:' . StoreTokenAbilities::CATEGORY_READ]);
        });
    });
