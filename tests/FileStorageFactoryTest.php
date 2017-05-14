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

use Projectmentor\Quota\Contracts\FileStorageInterface;
use Projectmentor\Quota\Contracts\PayloadInterface;
use Projectmentor\Quota\Contracts\FactoryInterface;
use Projectmentor\Quota\Stubs\RateData;
use Projectmentor\Quota\Factories\FileStorageFactory;

use bandwidthThrottle\tokenBucket\storage\FileStorage;

/**
 * This is a test case class.
 *
 * @author  David Faith <david@projectmentor.org>
 */
class FileStorageFactoryTest extends AbstractTestCase
{
    /**
     * Tear down after each test.
     * @return void
     */
    public function tearDown()
    {
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

    /**
     * @test
     * @group factories
     * @group factory_file_storage
     *
     * @expectedException bandwidthThrottle\tokenBucket\storage\StorageException
     */
    public function vfsThrowsStorageExceptionWhenInstantiatingFileStorageUsingBadFilename()
    {
        vfsStream::setup('test');
        @new FileStorage(vfsStream::url("test/nonexisting/test"));
    }

    /**
     * @test
     * @group factories
     * @group factory_file_storage
     *
     */
    public function itRemovesFileStorage()
    {
        $root = vfsStream::setup('test');
        $this->assertFalse($root->hasChild('data'));
        $storage = new FileStorage(vfsStream::url("test/data"));
        $this->assertTrue($root->hasChild('data'));
        $storage->remove();
        $this->assertFalse($root->hasChild('data'));
    }



    /**
     * @test
     * @group factories
     * @group factory_file_storage
     */
    public function testMake()
    {
        $factory = $this->getFactory();
        
        //Mock anonymous interfaces
        
        //Even though it seems like we need to mock a FileStorageData class;
        //we ultimately require a mocked PayloadInterface,
        //so that we can comply with FileStorageFactory
        //constructor declaration.
        
        //If we just try to mock a FileStorageData class
        //that receives getPath() method, It will fail the test
        //b/c $factory->make($data) requires a
        //PayloadInterface.
        $data = Mockery::mock(FileStorageInterface::class, PayloadInterface::class);

        //Use vfs to avoid memory-leak on open file handle
        //due to Mock holding reference which is not cleaned
        //until after the test is over.
        $dir = 'test';
        $root = vfsStream::setup($dir);
        $file = 'test-file';
        $path = vfsStream::url($dir . '/' . $file);

        $this->assertFalse($root->hasChild($path));

        $data->shouldReceive('getPath')
            ->once()
            ->andReturn($path)
            ->mock();

        $storage = $factory->make($data);
        $this->assertInstanceOf(FileStorage::class, $storage);
        $this->assertTrue($root->hasChild($file));
        
        //Normally, we would remove the storage when we want to delete
        //the file on disk, this clears the mutex, truncates and
        //unlinks the file  and resets isBootstrapped()
        $storage->remove();
        $this->assertFalse($root->hasChild($file));
    }

    protected function getFactory()
    {
        return new FileStorageFactory();
    }
}
