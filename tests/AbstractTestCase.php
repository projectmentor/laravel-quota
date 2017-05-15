<?php

/**
 * This file is part of laravel-quota
 *
 * (c) David Faith <david@projectmentor.org>
 *
 * Full copyright and license information is available
 * in the LICENSE file distributed with this source code.
 */

namespace Projectmentor\Quota\Tests;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
use Projectmentor\Quota\QuotaServiceProvider;

/**
 * This is the abstract test case class.
 *
 * @author  David Faith <david@projectmentor.org>
 */
abstract class AbstractTestCase extends AbstractPackageTestCase
{
    /**
     * Get the service provider class.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return string
     */
    protected function getServiceProviderClass($app)
    {
        return QuotaServiceProvider::class;
    }
}
