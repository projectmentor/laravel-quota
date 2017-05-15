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
 * This is the quota interface.
 */
interface Quota
{
    /**
     * Enforce a quota.
     */
    public function enforce();
}
