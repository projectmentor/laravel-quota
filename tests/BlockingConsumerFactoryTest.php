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

use Mockery;

use Projectmentor\Quota\Contracts\BlockingConsumerInterface;
use Projectmentor\Quota\Contracts\PayloadInterface;
use Projectmentor\Quota\Contracts\FactoryInterface;
use Projectmentor\Quota\Stubs\BlockingConsumerData;
use Projectmentor\Quota\Factories\BlockingConsumerFactory;

use bandwidthThrottle\tokenBucket\BlockingConsumer;
use bandwidthThrottle\tokenBucket\TokenBucket;

/**
 * This is a test case class.
 *
 * @author  David Faith <david@projectmentor.org>
 */
class BlockingConsumerFactoryTest extends AbstractTestCase
{

    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->assertTrue(true);
    }

    public function testMake()
    {
        $factory = $this->getFactory();

        $data = Mockery::mock(BlockingConsumerData::class);

        $bucket = Mockery::mock(TokenBucket::class);
        $data->shouldReceive('getBucket')->once()->andReturn($bucket);

        $result = $factory->make($data);
        $this->assertInstanceOf(BlockingConsumer::class, $result);
    }

    protected function getFactory()
    {
        return new BlockingConsumerFactory();
    }
}
