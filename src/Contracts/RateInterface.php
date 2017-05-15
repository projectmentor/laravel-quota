<?php

/**
 * This file is part of laravel-quota
 *
 * (c) David Faith <david@projectmentor.org>
 *
 * Full copyright and license information is available
 * in the LICENSE file distributed with this source code.
 */

namespace Projectmentor\Quota\Contracts;

/**
 * This is the quota factory interface.
 *
 * @author David Faith <david@projectmentor.org>
 */
interface RateInterface
{
    /**
     * Return the rate in tokens per second
     *
     * @return double
     */
    public function getTokensPerSecond();
}
