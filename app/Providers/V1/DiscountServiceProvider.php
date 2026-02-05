<?php

namespace App\Providers\V1;

use App\Contracts\V1\Discount\DiscountRepositoryInterface;
use App\Contracts\V1\Discount\DiscountServiceInterface;
use App\Repositories\V1\Discount\DiscountRepository;
use App\Services\V1\Discount\DiscountService;
use App\Services\V1\Discount\DiscountTypeValidatorRegistry;
use App\Services\V1\Discount\Validators\BuyXGetYFreeValidator;
use App\Services\V1\Discount\Validators\FirstOrderValidator;
use App\Services\V1\Discount\Validators\FixedAmountValidator;
use App\Services\V1\Discount\Validators\FreeDeliveryValidator;
use App\Services\V1\Discount\Validators\HappyHourValidator;
use App\Services\V1\Discount\Validators\PercentageValidator;
use App\Services\V1\Discount\Validators\QuantityValidator;
use App\Services\V1\Discount\Validators\ReduceDeliveryValidator;
use Illuminate\Support\ServiceProvider;

class DiscountServiceProvider extends ServiceProvider
{

    private const string DISCOUNT_VALIDATORS_TAG = 'discount.validators';

    public function register(): void
    {
        $this->app->bind(DiscountRepositoryInterface::class, fn($app) => new DiscountRepository());
        $this->app->bind(DiscountServiceInterface::class, DiscountService::class);

        $this->app->tag([
            FreeDeliveryValidator::class,
            BuyXGetYFreeValidator::class,
            FirstOrderValidator::class,
            FixedAmountValidator::class,
            FreeDeliveryValidator::class,
            HappyHourValidator::class,
            PercentageValidator::class,
            QuantityValidator::class,
            ReduceDeliveryValidator::class
        ], self::DISCOUNT_VALIDATORS_TAG);

        $this->app->singleton(DiscountTypeValidatorRegistry::class, function($app){
            return new DiscountTypeValidatorRegistry($app->tagged(self::DISCOUNT_VALIDATORS_TAG) ?? []);
        });
    }

    public function boot(): void
    {

    }
}
