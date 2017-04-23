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
 * This is the data payload contract.
 *
 * Initial properties for a new instance via
 * Projectmentor\Quota\Factories\FactoryInterface
 *
 * @author David Faith <david@projectmentor.org>
 */
interface PayloadInterface
{
    public function toJson();
    public function toArray();
}
