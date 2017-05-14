<?php

namespace Projectmentor\Quota\Tests;

/**
 * This file is part of laravel-quota
 *
 * (c) David Faith <david@projectmentor.org>
 *
 * Full copyright and license information is available
 * in the LICENSE file distributed with this source code.
 */

use Projectmentor\Quota\Quota;

/**
 * This is the quota test class.
 *
 * @author  David Faith <david@projectmentor.org>
 */
class QuotaTest extends AbstractTestCase
{
    protected $backup; //of config/quota.php

    /**
     * Setup before each test.
     * Backup and override the quota.config file.
     */
    public function setUp()
    {
        parent::setUp();

        $this->backup = \Config::get ('quota.connections');

        $override = [ 
            'test' => [
                'limit' => 2500,
                'period' => 'day',
                'log_table' => 'quotalog',
                'timezone' =>  'America/New_York',
            ],
        ];
        \Config::set('quota.connections', $override);
    }

    /**
     * Teardown after each test.
     * Restore the quota.config file.
     */
    public function tearDown()
    {
        \Config::set('quota.connections', $this->backup);
    }

    /**
     * A basic functional test example.
     *
     * @test
     * @group classes
     * @group classes_quota
     * @group classes_quota_base
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     * @group classes
     * @group classes_quota
     * @group classes_quota_base
     */
    public function testConstructor()
    {
        $quota = new Quota('test');
        $this->assertEquals('2500', $quota->getLimit());
        $this->assertEquals('day', $quota->getPeriod());
        $this->assertEquals('quota.connections.test', $quota->getIndex());
        $this->assertEquals('test', $quota->getConnection());
    }

    /**
     * @test
     * @group classes
     * @group classes_quota
     * @group classes_quota_base
     */
    public function testInvalidPeriodReturnsFalse()
    {
        $quota = new Quota('test');
        $this->assertFalse($quota->validatePeriod('invalid-period'));
    }

    /**
     * @test
     * @group classes
     * @group classes_quota
     * @group classes_quota_base
     */
   public function testValidPeriodsReturnsRateConstants()
   {
        $reference = \bandwidthThrottle\tokenBucket\Rate::class;
        $reflector = new \ReflectionClass($reference);
        $expected = json_encode($reflector->getConstants());

        $quota = new Quota('test');
        $result = json_encode($quota->validPeriods());

        $this->assertEquals($expected, $result);
   } 

    /**
     * @test
     * @group classes
     * @group classes_quota
     * @group classes_quota_base
     */
   public function testEnforceThrowsException()
   {    
        $quota = new Quota('test');
        $this->expectException(\Exception::class);
        $quota->enforce();
   }
}

