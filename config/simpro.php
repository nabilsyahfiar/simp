<?php

return [
    'force_https' => env('FORCE_HTTPS', env('APP_ENV') === 'production'),

    'trust_proxies' => env('TRUST_PROXIES', false),

    'trusted_proxies' => env('TRUSTED_PROXIES', '*'),

    'image' => [
        'max_dimension' => (int) env('IMAGE_MAX_DIMENSION', 1920),
        'jpeg_quality' => (int) env('IMAGE_JPEG_QUALITY', 82),
        'png_compression' => (int) env('IMAGE_PNG_COMPRESSION', 7),
    ],
];
