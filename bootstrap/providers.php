<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\CatalogServiceProvider::class,
    App\Providers\V1\CategoryServiceProvider::class,
    App\Providers\V1\ItemServiceProvider::class,
    App\Providers\V1\RateLimitServiceProvider::class,
    App\Providers\V1\StoreMemberAuthServiceProvider::class,
    App\Providers\V1\StoreMemberServiceProvider::class,
    App\Providers\V1\StoreServiceProvider::class,
];
