<?php
return [
    'currency' => env('POS_CURRENCY', 'DZD'),
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
    ]
];
