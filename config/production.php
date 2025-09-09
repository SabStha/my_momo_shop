<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Production Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains production-specific configurations for the Momo Shop
    | application. These settings optimize performance and security for
    | production environments.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'force_https' => env('FORCE_HTTPS', true),
        'secure_cookies' => env('SESSION_SECURE_COOKIE', true),
        'http_only_cookies' => env('SESSION_HTTP_ONLY', true),
        'same_site_cookies' => env('SESSION_SAME_SITE', 'strict'),
        'bcrypt_rounds' => env('BCRYPT_ROUNDS', 12),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'cache_ttl' => env('CACHE_TTL', 3600), // 1 hour
        'session_timeout' => env('SESSION_TIMEOUT', 7200), // 2 hours
        'max_execution_time' => 300, // 5 minutes
        'memory_limit' => '512M',
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'max_file_size' => env('MAX_FILE_SIZE', 10240), // 10MB in KB
        'allowed_types' => explode(',', env('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,webp,avif')),
        'image_quality' => 85,
        'thumbnail_size' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'level' => env('LOG_LEVEL', 'error'),
        'max_files' => 14, // Keep 14 days of logs
        'max_size' => '10M',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        'api_requests' => 100, // requests per minute
        'login_attempts' => 5, // attempts per minute
        'password_reset' => 3, // attempts per hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Settings
    |--------------------------------------------------------------------------
    */
    'backup' => [
        'enabled' => env('BACKUP_ENABLED', true),
        'retention_days' => 30,
        'compress' => true,
        'notifications' => [
            'enabled' => env('BACKUP_NOTIFICATIONS', true),
            'email' => env('BACKUP_NOTIFICATION_EMAIL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Settings
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'health_check' => [
            'enabled' => true,
            'endpoint' => '/health',
            'checks' => [
                'database' => true,
                'redis' => true,
                'storage' => true,
            ],
        ],
        'error_tracking' => [
            'enabled' => env('ERROR_TRACKING_ENABLED', false),
            'service' => env('ERROR_TRACKING_SERVICE', 'sentry'),
        ],
    ],
];
