<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PDS Cache Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains settings for PDS caching to improve
    | performance and reduce database load.
    |
    */

    'pds' => [
        // Cache TTL in seconds (default: 5 minutes)
        'ttl' => env('PDS_CACHE_TTL', 300),

        // Maximum number of rows per dynamic table (for pagination)
        'max_rows_per_table' => 45,

        // Maximum session data size in KB
        'max_session_size_kb' => 1024,

        // Enable query result caching
        'enable_query_cache' => env('PDS_ENABLE_QUERY_CACHE', true),

        // Cache driver to use (null, file, database, redis, memcached)
        'driver' => env('PDS_CACHE_DRIVER', env('CACHE_STORE', 'file')),
    ],

    'redis' => [
        'connection' => env('PDS_REDIS_CONNECTION', 'cache'),
        'lock_connection' => env('PDS_REDIS_LOCK_CONNECTION', 'default'),
    ],
];
