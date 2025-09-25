<?php

use App\Constants\V1\StoreTokenAbilities;
use App\Http\Controllers\V1\IngredientController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/ingredients')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate'])
    ->group(function () {
        Route::post('/', [IngredientController::class, 'store'])
            ->middleware(['store_member', 'ability:' . StoreTokenAbilities::CREATE_INGREDIENT]);

        Route::get('/', [IngredientController::class, 'index'])
            ->middleware('ability:' . StoreTokenAbilities::INGREDIENT_READ);

        Route::get('/{ingredient}', [IngredientController::class, 'show'])
            ->middleware('ability:' . StoreTokenAbilities::INGREDIENT_READ);

        Route::match(['put', 'patch'], '/{ingredient}', [IngredientController::class, 'update'])
            ->middleware(['store_member', 'ability:' . StoreTokenAbilities::UPDATE_INGREDIENT]);

        Route::delete('/{ingredient}', [IngredientController::class, 'destroy'])
            ->middleware(['store_member', 'ability:' . StoreTokenAbilities::INGREDIENT_DELETE]);
    });
