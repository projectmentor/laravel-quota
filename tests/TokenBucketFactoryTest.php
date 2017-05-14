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

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;

use Projectmentor\Quota\Contracts\TokenBucketInterface;
use Projectmentor\Quota\Contracts\PayloadInterface;
use Projectmentor\Quota\Contracts\FactoryInterface;
use Projectmentor\Quota\Stubs\TokenBucketData;
use Projectmentor\Quota\Factories\TokenBucketFactory;

use bandwidthThrottle\tokenBucket\TokenBucket;
use bandwidthThrottle\tokenBucket\Rate;
use bandwidthThrottle\tokenBucket\storage\FileStorage;

/**
 * This is a test case class.
 *
 * @author  David Faith <david@projectmentor.org>
 */
class TokenBucketFactoryTest extends AbstractTestCase
{
    protected $dir;
    protected $root;
    protected $file;
    protected $path;
    protected $fileStorage;

    protected $limit;
    protected $period;

    public function setUp()
    {
        parent::setUp();

        //For dependency fileStorage
        $this->dir = 'test';
        $this->root = vfsStream::setup($this->dir);
        $this->file = 'test-file';
        $this->path = vfsStream::url($this->dir . '/' . $this->file);
        $this->fileStorage = $this->getFileStorage();
        $this->assertTrue($this->root->hasChild($this->file));

        //for dependency Rate
        $this->limit = 10;
        $this->period = Rate::SECOND;
    }

    public function tearDown()
    {
        $this->fileStorage->remove();
        $this->assertFalse($this->root->hasChild($this->file));
        parent::tearDown();
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


    public function testMake()
    {
        $factory = $this->getFactory();
        $rate = $this->getRate();

        $data = Mockery::mock(TokenBucketData::class);
        $data->shouldReceive('getCapacity')->once()->andReturn(10);

        $data->shouldReceive('getRate')->once()->andReturn($rate);

        $data->shouldReceive('getStorage')
            ->once()
            ->andReturn($this->fileStorage);

        $bucket = $factory->make($data);
        $this->assertInstanceOf(TokenBucket::class, $bucket);
    }

    protected function getRate()
    {
        return new Rate($this->limit, $this->period);
    }

    protected function getFileStorage()
    {
        return new FileStorage($this->path);
    }

    protected function getFactory()
    {
        return new TokenBucketFactory();
    }
}
