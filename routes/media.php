<?php

use App\Constants\V1\StoreTokenAbilities;
use App\Http\Controllers\V1\MediaController;
use App\Models\V1\Category;
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
        'categories'   => Category::class,
        'store'        => Store::class,
        'store-member' => StoreMember::class,
        'store_member' => StoreMember::class,
        'device'       => Device::class,
        'ingredient'   => Ingredient::class,
        'option'       => Option::class,
        'item_variant' => ItemVariant::class,
    ];

    if (! isset($map[$type])) {
        abort(404, "Type de ressource non supporté : $type");
    }

    /** @var Model $class */
    $class = $map[$type];

    return $class::query()->findOrFail($value);
});

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate', 'store_member'])
    ->group(function () {

    Route::get('{type}/{modelBinding}/media/main', [MediaController::class, 'showMain'])
        ->name('media.main.show')
        ->middleware(['ability:' . StoreTokenAbilities::MEDIA_VIEW, 'has_media:main']);
    Route::post('{type}/{modelBinding}/media/main', [MediaController::class, 'storeMain'])
        ->name('media.main.store')
        ->middleware(['ability:' . StoreTokenAbilities::MEDIA_UPLOAD, 'has_media:main']);
    Route::delete('{type}/{modelBinding}/media/main', [MediaController::class, 'destroyMain'])
        ->name('media.main.destroy')
        ->middleware(['ability:' . StoreTokenAbilities::MEDIA_DELETE, 'has_media:main']);

    Route::get('{type}/{modelBinding}/media/gallery', [MediaController::class, 'indexGallery'])
        ->name('media.gallery.index')
        ->middleware(['ability:' . StoreTokenAbilities::MEDIA_VIEW, 'has_media:gallery']);
    Route::post('{type}/{modelBinding}/media/gallery', [MediaController::class, 'storeGallery'])
        ->name('media.gallery.store')
        ->middleware(['ability:' . StoreTokenAbilities::MEDIA_UPLOAD, 'has_media:gallery']);
    Route::delete('{type}/{modelBinding}/media/gallery/{media}', [MediaController::class, 'destroyGallery'])
        ->name('media.gallery.destroy')
        ->middleware(['ability:' . StoreTokenAbilities::MEDIA_DELETE, 'has_media:gallery']);
});

