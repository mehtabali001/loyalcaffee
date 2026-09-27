<?php

return [
    // Supported content locales. First one is the default.
    'locales' => ['en', 'ar'],
    'rtl_locales' => ['ar'],

    // Public API responses are cached until content changes (see HasContentVersion)
    'cache_ttl' => 60 * 60 * 24,

    // Public pages the SPA serves, used for SEO meta + sitemap
    'pages' => ['home', 'menu', 'gallery', 'match-nights', 'reserve', 'contact'],

    'uploads' => [
        'disk' => 'public',
        'max_kb' => 6144,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp', 'avif', 'svg', 'mp4', 'webm'],
    ],
];
