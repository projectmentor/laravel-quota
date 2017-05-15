<?php

/**
 * This file is part of laravel-quota
 *
 * (c) David Faith <david@projectmentor.org>
 *
 * Full copyright and license information is available
 * in the LICENSE file distributed with this source code.
 */

namespace Projectmentor\Quota\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * This is the quota facade class.
 * To use it, insert the following key => value pair:
 *
 *'Quota' => Projectmentor\Quota\Facades\Quota::class,
 *
 * in the 'aliases' array
 * in the host laravel project's config/app.php
 *
 * @author David Faith <david@projectmentor.org>
 */

class Quota extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'quota';
    }
}
