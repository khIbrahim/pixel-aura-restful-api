<?php

return [
    'collections' => [
        'category_images' => [
            'disk' => 's3',
            'conversions' => [
                'thumbnail'   => [
                    'width'   => 150,
                    'height'  => 150,
                    'quality' => 80
                ],
                'responsive'  => [
                    'enabled' => true,
                    'sizes'   => [320, 640, 768, 1024, 1280]
                ],
                'optimization'  => [
                    'webp'      => true,
                    'lazy_load' => true
                ]
            ]
        ]
    ],

    'validation' => [
        'max_size'   => 5 * 1024,
        'mime_types' => ['jpg', 'jpeg', 'png', 'webp'],
        'dimensions' => [
            'min_width'  => 100,
            'min_height' => 100,
            'max_width'  => 5000,
            'max_height' => 5000
        ]
    ],

    'storage' => [
        'preserve_original' => true,
        'cleanup_old_media' => true,
    ],

    'url_processing' => [
        'enabled'         => true,
        'timeout'         => 30,
        'max_redirects'   => 5,
        'user_agent'      => 'PixelAura/1.0',
        'allowed_domains' => [

        ],

        'blocked_domains' => [
            'localhost', '127.0.0.1', '0.0.0.0', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'
        ],

        'temp_storage' => [
            'disk' => 'local',
            'path' => 'temp/url-images',
            'cleanup_after' => 3600,
        ],
        'security' => [
            'scan_for_malware'    => false, // Intégration future avec ClamAV
            'max_file_size'       => 10 * 1024 * 1024, // 10MB
            'check_image_headers' => true,
        ]
    ]
];
