<?php

use App\Http\Controllers\V1\MediaController;
use App\Models\V1\Device;
use App\Models\V1\Ingredient;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use App\Models\V1\Option;
use App\Models\V1\Store;
use App\Models\V1\StoreMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

Route::bind('modelBinding', function ($value, $route) {
    $type = $route->parameter('type');

    $map = [
        'items'        => Item::class,
        'store'        => Store::class,
        'store-member' => StoreMember::class,
        'store_member' => StoreMember::class,
        'device'       => Device::class,
        'ingredient'   => Ingredient::class,
        'option'       => Option::class,
        'item_variant' => ItemVariant::class,
    ];

    if (! isset($map[$type])) {
        abort(404, "Type de ressource non supporté : {$type}");
    }

    /** @var Model $class */
    $class = $map[$type];

    return $class::query()->findOrFail($value);
});

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate', 'store_member'])
    ->group(function () {

    // =========================
    // Main image routes
    // =========================
    Route::get('{type}/{modelBinding}/media/main', [MediaController::class, 'showMain']);
    Route::post('{type}/{modelBinding}/media/main', [MediaController::class, 'storeMain']);
    Route::delete('{type}/{modelBinding}/media/main', [MediaController::class, 'destroyMain']);

    Route::get('{type}/{modelBinding}/media/gallery', [MediaController::class, 'indexGallery']);
    Route::post('{type}/{modelBinding}/media/gallery', [MediaController::class, 'storeGallery']);
    Route::delete('{type}/{modelBinding}/media/gallery/{media}', [MediaController::class, 'destroyGallery']);
});

