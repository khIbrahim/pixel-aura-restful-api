<?php

namespace App\Providers\V1;

use App\Contracts\V1\Category\CategoryRepositoryInterface;
use App\Contracts\V1\Category\CategoryServiceInterface;
use App\Repositories\V1\Category\CachedCategoryRepository;
use App\Repositories\V1\Category\CategoryRepository;
use App\Services\V1\Category\CategoryService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CategoryServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, function ($app) {
            return new CachedCategoryRepository(
                new CategoryRepository()
            );
        });

        $this->app->bind(
            CategoryServiceInterface::class,
            CategoryService::class
        );

        Route::bind('category', function ($value) {
            $storeId = request()->user()->store_id;

            if (is_numeric($value)) {
                return app(CategoryRepositoryInterface::class)->findById((int) $value);
            }

            return app(CategoryRepositoryInterface::class)->findBySlug((string) $value, $storeId);
        });
    }

    public function boot(): void
    {

    }
}
