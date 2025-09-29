<?php

use App\Models\V1\Category;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;

return [
    'length'         => env('SKU_LENGTH', 24),
    'variant_length' => env('SKU_VARIANT_LENGTH', 8),
    'separator'      => env('SKU_SEPARATOR', '-'),
    'case'           => env('SKU_CASE', 'upper'), // upper, lower, mixed
    'prefix_enabled' => env('SKU_PREFIX_ENABLED', true),
    'suffix_enabled' => env('SKU_SUFFIX_ENABLED', false),
    'allowed_chars'  => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',

    'cache_ttl'    => env('SKU_CACHE_TTL', 300), // 5 minutes
    'max_attempts' => env('SKU_MAX_ATTEMPTS', 20),

    'models' => [
        Item::class => [
            'prefix_pattern' => 'ST{store_id:3}', // ST001-PRODUCT-NAME
            'length'         => 20,
        ],
        ItemVariant::class => [
            'inherit_from_parent' => true,
            'suffix_pattern'      => '{variant_name:8}',
        ],
        Category::class => [
            'prefix' => 'CAT',
            'length' => 16,
        ],
    ],
];
