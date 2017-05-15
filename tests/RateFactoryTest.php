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

use Projectmentor\Quota\Contracts\RateInterface;
use Projectmentor\Quota\Contracts\PayloadInterface;
use Projectmentor\Quota\Contracts\FactoryInterface;
use Projectmentor\Quota\Stubs\RateData;
use Projectmentor\Quota\Factories\RateFactory;

use bandwidthThrottle\tokenBucket\Rate;

/**
 * This is a test case class.
 *
 * @author  David Faith <david@projectmentor.org>
 */
class RateFactoryTest extends AbstractTestCase
{
    /**
     * @group factory
     * @group factory_rate
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->assertTrue(true);
    }

    /**
     * @group factory
     * @group factory_rate
     *
     * @return void
     */
    public function testMake()
    {
        $factory = $this->getFactory();

        $data = Mockery::mock(RateData::class);
        $data->shouldReceive('getLimit')->once()->andReturn(10);
        $data->shouldReceive('getPeriod')->once()->andReturn(Rate::SECOND);

        $result = $factory->make($data);
        $this->assertInstanceOf(Rate::class, $result);
    }

    /**
     * @group factory
     * @group factory_rate
     *
     * @return void
     */
    public function testGetConstants()
    {
        $factory = $this->getFactory();
        $constants = $factory->getConstants();
        $this->assertEquals('year', $constants['YEAR']);
    }

    /**
     * @group factory
     * @group factory_rate
     *
     * @return void
     */
    public function testGetConstant()
    {
        $factory = $this->getFactory();
        $result = $factory->getConstant('MICROSECOND');
        $this->assertEquals('microsecond', $result);

        $this->expectException(\InvalidArgumentException::class);
        $factory->getConstant('invalid constant name');
    }

    /**
     * Helper.
     * @return Projectmentor\Quote\Factories\RateFactory
     */
    protected function getFactory()
    {
        return new RateFactory();
    }
}
