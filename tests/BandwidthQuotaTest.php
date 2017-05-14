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

use Projectmentor\Quota\BandwidthQuota;

use throttleBandwidth\tokenBucket\storage\FileStorage;
use throttleBandwidth\tokenBucket\Rate;
use throttleBandwidth\tokenBucket\TokenBucket;
use throttleBandwidth\tokenBucket\BlockingConsumer;

/**
 * This is the quota test class.
 *
 * @author  David Faith <david@projectmentor.org>
 */
class BandwidthQuotaTest extends AbstractTestCase
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
                'limit' =>  60,
                'period' => 'second',
                'driver' => 'quota.storage.file',
                'path' => '/tmp/bandwidth.quota.test',
                'capacity' =>  60,
                'block' => true
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
     * @group classes_quota_bandwidth
     */
    public function testConstructor()
    {
        $connection = 'test';
        $quota = new BandwidthQuota($connection);

        $index = $quota->getIndex();
        $this->assertEquals('quota.connections.test', $index);

        $limit = $quota->getLimit();
        $this->assertEquals(60, $limit);

        $period = $quota->getPeriod();
        $this->assertEquals('second', $period);

        $config_handle = $quota->getConnection();
        $this->assertEquals($connection, $config_handle);

        $storage = $quota->getStorage();

        $this->assertNotNull($storage);
        $this->assertEquals('bandwidthThrottle\tokenBucket\storage\FileStorage',
            get_class($storage));

        $rate = $quota->getRate();
        $this->assertNotNull($rate);
        $this->assertEquals('bandwidthThrottle\tokenBucket\Rate',
            get_class($rate));

        $bucket = $quota->getBucket();
        $this->assertNotNull($bucket);
        $this->assertEquals('bandwidthThrottle\tokenBucket\TokenBucket',
            get_class($bucket));

        $blocker = $quota->getBlocker();
        $this->assertNotNull($blocker);
        $this->assertEquals('bandwidthThrottle\tokenBucket\BlockingConsumer',
            get_class($blocker));
    }

    /**
     * @test
     * @group classes
     * @group classes_quota
     * @group classes_quota_bandwidth
     */
   public function testBandwidthQuotaBlocks()
   {
        $quota = new BandwidthQuota('test');
        $blocker = $quota->getBlocker();

        $time_start = microtime(true);

        //underquota won't block.
        $blocker->consume(1); 
        
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        
        $this->assertTrue($time < 1);

        usleep(1000);
        
        $time_start = microtime(true);
        
        $blocker->consume(60);

        //overquota should block. 
        $blocker->consume(60); 
        
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        
        $this->assertTrue($time > 1);

   } 
}

