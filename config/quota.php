<?php

/**
 * This file is part of laravel-quota
 *
 * (c) David Faith <david@projectmentor.org>
 *
 * Full copyright and license information is available
 * in the LICENSE file distributed with this source code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Quota default timezone authority
    |--------------------------------------------------------------------------
    |
    | Used to determine when to reset limits in quotalog table.
    |
    */

    'default_timezone' => 'America/New_York',

    /*
    |--------------------------------------------------------------------------
    | Quota Connections
    |--------------------------------------------------------------------------
    |
    | The quota connections for your application.
    |
    */

    'connections' => [

        'bandwidth' => [
            'limit' => env('QUOTA_BANDWIDTH_LIMIT', 60),
            'period' => env('QUOTA_BANDWIDTH_PERIOD','second'),
            'driver' => 'quota.storage.file',
            'path' => env('QUOTA_BANDWIDTH_PATH', '/tmp/bandwidth.quota'),
            'capacity' => env('QUOTA_BANDWIDTH_CAPACITY', 60),
            'block' => env('QUOTA_BANDWIDTH_BLOCK', true)
        ],

        'daily' => [
            'limit' => env('QUOTA_DAILY_LIMIT', 2500),
            'period' => env('QUOTA_DAILY_PERIOD','day'),
            'log_table' => env('QUOTA_DAILY_LOG_TABLE', 'quotalog'),
            'timezone' => env('QUOTA_DAILY_TIMEZONE', 'America/New_York'),
        ],
    ]
];
