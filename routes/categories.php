<?php

use App\Constants\V1\StoreTokenAbilities;
use App\Http\Controllers\V1\CategoriesController;
use App\Http\Controllers\V1\CategoryOptionListsController;
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

        // OPTION LISTS
        Route::get('{category}/option-lists', [CategoryOptionListsController::class, 'index'])
            ->name('item_attachments.option_lists.index');

        Route::post('{category}/option-lists', [CategoryOptionListsController::class, 'attach'])
            ->name('item_attachments.option_lists.attach');

        Route::delete('{category}/option-lists/{optionList}', [CategoryOptionListsController::class, 'detach'])
            ->name('item_attachments.option_lists.detach');
    });
