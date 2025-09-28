<?php

namespace App\Providers\V1;

use App\Contracts\V1\Ingredient\IngredientRepositoryInterface;
use App\Contracts\V1\Ingredient\IngredientServiceInterface;
use App\Contracts\V1\Item\ItemAttachmentServiceInterface;
use App\Contracts\V1\Item\ItemRepositoryInterface;
use App\Contracts\V1\Item\ItemServiceInterface;
use App\Contracts\V1\ItemVariant\ItemVariantRepositoryInterface;
use App\Contracts\V1\ItemVariant\ItemVariantServiceInterface;
use App\Contracts\V1\Option\OptionRepositoryInterface;
use App\Contracts\V1\Option\OptionServiceInterface;
use App\Contracts\V1\OptionList\OptionListRepositoryInterface;
use App\Contracts\V1\OptionList\OptionListServiceInterface;
use App\Repositories\V1\Ingredient\CachedIngredientRepository;
use App\Repositories\V1\Ingredient\IngredientRepository;
use App\Repositories\V1\Item\ItemRepository;
use App\Repositories\V1\ItemVariant\ItemVariantRepository;
use App\Repositories\V1\Option\OptionRepository;
use App\Repositories\V1\OptionList\OptionListRepository;
use App\Services\V1\Ingredient\IngredientService;
use App\Services\V1\Ingredient\OptionService;
use App\Services\V1\Item\ItemAttachmentService;
use App\Services\V1\Item\ItemService;
use App\Services\V1\ItemVariant\ItemVariantService;
use App\Services\V1\OptionList\OptionListService;
use Illuminate\Support\ServiceProvider;

class ItemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        $this->app->bind(ItemServiceInterface::class, ItemService::class);

        $this->app->bind(ItemAttachmentServiceInterface::class, ItemAttachmentService::class);

        $this->app->bind(IngredientRepositoryInterface::class, fn ($app) => new CachedIngredientRepository(new IngredientRepository));
        $this->app->bind(IngredientServiceInterface::class, IngredientService::class);

        $this->app->bind(OptionRepositoryInterface::class, fn ($app) => new OptionRepository);
        $this->app->bind(OptionServiceInterface::class, OptionService::class);

        $this->app->bind(OptionListRepositoryInterface::class, fn ($app) => new OptionListRepository);
        $this->app->bind(OptionListServiceInterface::class, OptionListService::class);

        $this->app->bind(ItemVariantRepositoryInterface::class, fn ($app) => new ItemVariantRepository);
        $this->app->bind(ItemVariantServiceInterface::class, ItemVariantService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
