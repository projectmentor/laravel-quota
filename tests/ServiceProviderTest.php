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

use GrahamCampbell\TestBenchCore\ServiceProviderTrait;
use Projectmentor\Quota\Contracts\PayloadInterface;
use Projectmentor\Quota\Contracts\FactoryInterface;
use Projectmentor\Quota\Stubs\RateData;
use Projectmentor\Quota\Stubs\FileStorageData;
use Projectmentor\Quota\Stubs\TokenBucketData;
use Projectmentor\Quota\Stubs\BlockingConsumerData;
use Projectmentor\Quota\Factories\RateFactory;
use Projectmentor\Quota\Factories\FileStorageFactory;
use Projectmentor\Quota\Factories\TokenBucketFactory;
use Projectmentor\Quota\Factories\BlockingConsumerFactory;

/**
 * This is a test case class.
 *
 * @author  David Faith <david@projectmentor.org>
 */
class ServiceProviderTest extends AbstractTestCase
{
    use ServiceProviderTrait;

    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->assertTrue(true);
    }

    public function testRateFactoryIsInjectable()
    {
        $this->assertIsInjectable(RateFactory::class);

        //TODO: RESEARCH
        //These are not automatically instantiable.
        //Is it b/c no alias registered in service provider?
        //
        //$this->assertIsInjectable(PayloadInterface::class);
        //$this->assertIsInjectable(FactoryInterface::class);
    }

    public function testFileStorageFactoryIsInjectable()
    {
        $this->assertIsInjectable(FileStorageFactory::class);
    }

    public function testTokenBucketFactoryIsInjectable()
    {
        $this->assertIsInjectable(TokenBucketFactory::class);
    }

    public function testBlockingConsumerFactoryIsInjectable()
    {
        $this->assertIsInjectable(BlockingConsumerFactory::class);
    }
}
