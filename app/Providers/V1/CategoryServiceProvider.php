<?php

namespace App\Providers\V1;

use App\Contracts\V1\Category\CategoryRepositoryInterface;
use App\Contracts\V1\Category\CategoryServiceInterface;
use App\Exceptions\V1\Category\CategoryNotFoundException;
use App\Repositories\V1\Category\CategoryRepository;
use App\Services\V1\Category\CategoryService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CategoryServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, fn ($app) => new CategoryRepository());

        $this->app->bind(
            CategoryServiceInterface::class,
            CategoryService::class
        );

        Route::bind('category', function ($value) {
            $storeId = request()->user()->store_id;

            if (is_numeric($value)) {
                return app(CategoryRepositoryInterface::class)->find((int) $value) ?? throw CategoryNotFoundException::withId((int) $value);
            }

            if(is_string($value)) {
                return app(CategoryRepositoryInterface::class)->findBySlug($value, $storeId) ?? throw CategoryNotFoundException::withSlug($value);
            }

            return CategoryNotFoundException::default();
        });
    }

    public function boot(): void
    {

    }
}
