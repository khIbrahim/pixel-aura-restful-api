<?php
return [
    'currency' => env('POS_CURRENCY', 'DZD'),
    'locale'   => env('POS_LOCALE', 'fr_DZ'),
    'item' => [
        'sku' => [
            'base_length'    => 24,
            'variant_length' => 8,
            'max_attempts'   => 10,
            'separator'      => '-',
        ],
        'media' => [
            'collections' => ['thumbnail', 'preview'],
            'conversions' => [
                'thumbnail' => ['fit' => 'crop',    'width' => 300,  'height'   => 300],
                'preview'   => ['fit' => 'contain', 'width' => 800,  'height'   => null],
                'banner'    => ['fit' => 'crop',    'width' => 1200, 'height'   => 600],
                'icon'      => ['fit' => 'crop',    'width' => 64,   'height'   => 64],
            ]
        ],
    ],
    'order' => [
        'preparation_time' => [
            'default_minutes' => 15,
            'rush_hour' => [
                'enabled'          => true,
                'start'            => '11:00',
                'end'              => '14:00',
                'days'             => [1, 2, 3, 4, 5],
                'extra_multiplier' => 1.5
            ],
            'bulk_order' => [
                'enabled'       => true,
                'threshold'     => 10,
                'extra_minutes' => 10
            ],
            'kitchen_load' => [
                'enabled'          => true,
                'check_interval'   => 15, // en minutes
                'high_load_orders' => 5,  // nombre d'ordres en cours pour considérer une charge élevée
                'extra_minutes'    => 10
            ]
        ]
    ]
];
