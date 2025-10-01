<?php

namespace App\Providers;

use App\Models\V1\Category;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use App\Models\V1\Option;
use App\Observers\V1\CategoryObserver;
use App\Observers\V1\ItemObserver;
use App\Observers\V1\ItemVariantObserver;
use App\Repositories\V1\Store\StoreRepository;
use App\Services\V1\Catalog\CatalogCacheService;
use App\Services\V1\Catalog\CatalogService;
use App\Services\V1\Catalog\CatalogVersionManager;
use App\Services\V1\Catalog\Formatters\CompactFormatter;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(CatalogCacheService::class, fn($app) => new CatalogCacheService());
        $this->app->bind(CatalogVersionManager::class, fn($app) => new CatalogVersionManager(app(CatalogCacheService::class), app(StoreRepository::class)));
        $this->app->bind(CatalogService::class, fn($app) => new CatalogService(
            new CompactFormatter()
        ));

        Item::observe(ItemObserver::class);
        Category::observe(CategoryObserver::class);
        ItemVariant::observe(ItemVariantObserver::class);
        Option::observe(Option::class);
    }


    public function boot(): void
    {
        //
    }
}
