<?php

/**
 * This file is part of laravel-quota
 *
 * (c) David Faith <david@projectmentor.org>
 *
 * Full copyright and license information is available
 * in the LICENSE file distributed with this source code.
 */

namespace Projectmentor\Quota;

use \bandwidthThrottle\tokenBucket\storage\FileStorage;
use \bandwidthThrottle\tokenBucket\Rate;
use \bandwidthThrottle\tokenBucket\TokenBucket;
use \bandwidthThrottle\tokenBucket\BlockingConsumer;

/**
 * This is the BandwidthQuota class.
 * It provides rate-of-flow quota restriction
 * e.g. limit api requests to 60/second.
 *
 * Currently only support FileStorage type
 * of persistance.
 */

class BandwidthQuota extends Quota
{
    /**
     * The driver shortcut name as known to the IoC.
     *
     * @var string
     */
    protected $driver;

    /**
     * The file path, if driver resolves to FileStorage
     *
     * @var string
     */
    protected $path;

    /**
     * The persistant storage for the bucket.
     *
     * @var mixed
     */
    protected $storage;

    /**
     * The rate
     *
     * @var \bandwidthThrottle\tokenBucket\Rate
     */
    protected $rate;

    /**
     * The bucket
     *
     * @var \bandwidthThrottle\tokenBucket\TokenBucket
     */
    protected $bucket;

    /**
     * The blocking consumer
     *
     * @var \bandwidthThrottle\tokenBucket\BlockingConsumer
     */
    protected $blocker;

    /**
     *  Construct instance.
     *
     *  NOTE: Laravel 5.4 breaking change:
     *      App::make(...) ~> App::makeWith(...)
     *
     *  TODO: REFACTOR Currently only supports FileStorage.
     *  Also, resolving from container with parameters is not
     *  best practice.
     *  @See https://github.com/laravel/internals/issues/391
     *
     *  @param string $connection
     *  @return void
     */
    public function __construct($connection)
    {
        parent::__construct($connection);

        $this->driver = config($this->index . '.driver');

        //TODO: REFACTOR for multiple storage types.
        if ($this->driver != 'quota.storage.file') {
            throw new \Exception('Driver: ' . $this->driver . ' not supported.');
        }

        $this->path = config($this->index . '.path');
        $this->storage = app($this->driver, ['path' =>  $this->path]);
        //END REFACTOR

        //Resolve rate via IoC container.
        $this->rate = app(
            'quota.rate',
            ['limit' => $this->limit, 'period' => $this->period]
        );

        //Resolve bucket via IoC container.
        $capacity = config($this->index . '.capacity');
        $this->bucket = app('quota.bucket', [
            'capacity' => $capacity,
            'rate' => $this->rate,
            'storage' => $this->storage
        ]);

        //Bootstrap storage if not already initialized.
        $this->bucket->bootstrap($capacity);

        //Optionally enclose bucket within a blocking consumer.
        if (config($this->index . '.block') == true) {
            $this->blocker = app('quota.blocker', ['bucket' => $this->bucket]);
        }
    }

    /**
     * Enforce the quota.
     * Future public interface
     *
     * @return boolean true on success
     *
     * @throws ErrorException
     * Overquota exceeds bandwidth rate.
     *
     * @throws LengthException
     *  The token amount is larger than the bucket's capacity.
     *
     * @throws StorageException
     * The stored microtime could not be accessed.
     */
    public function enforce()
    {
        return $this->consume();
    }

    /**
     * Consume tokens.
     *
     * If  $this->bucket does not have sufficient tokens
     * and $this->block is true, then block until available.
     *
     * If this->bucket does not have sufficient tokens
     * and $this->block is false, the throw exception.
     *
     * If this->bucket has sufficent tokens, then:
     *
     *    If $this is logging to database then:
     *      If  hits exceed periodic limit then:
     *          increment misses in the database
     *          throw exception.
     *      Else
     *          increment hits in database
     *
     *    reduce the amount of tokens in the bucket
     *    return  true.
     *
     * @param int $tokens default  === 1
     * @return boolean true on success
     *
     * @throws ErrorException
     * Overquota exceeds bandwidth rate.
     *
     * @throws LengthException
     *  The token amount is larger than the bucket's capacity.
     *
     * @throws StorageException
     * The stored microtime could not be accessed.
     */
    public function consume($tokens = 1)
    {
        if (isset($this->bucket)) {
            if (! isset($this->blocker)) {
                $result = $this->bucket->consume($tokens, $seconds);
                if ($result === false) {
                    throw new \ErrorException(
                        __CLASS__ . '::' . __FUNCTION__ .
                        ' Overquota. Exceeded bandwidth. Wait ' . $seconds . ' seconds.'
                    );
                }
            } else {
                $result = $this->blocker->consume($tokens);
            }
        }
        return (isset($result)) ? $result : false;
    }

    /**
     * Clean up before removing storage for gc.
     * Releases $blocker, $bucket, $rate
     * Does not release $storage.
     * Does not unlink file storage file.
     *
     * @return void
     */
    public function release()
    {
        if (isset($this->blocker)) {
            unset($this->blocker);
        }

        if (isset($this->bucket)) {
            unset($this->bucket);
        }

        if (isset($this->rate)) {
            unset($this->rate);
        }
    }

    /**
     * Unlink the handle to storage then release storage.
     * e.g: if file storage, then unlink (delete) file on disk.
     *
     * @return void
     */
    public function remove()
    {
        if (isset($this->storage)) {
            $this->storage->remove();
            unset($this->storage);
        }
    }

    /**
     * Orderly clean up.
     *
     * @internal
     */
    public function __destruct()
    {
        $this->release();
        $this->remove();
    }

    /**
     * Get the driver IoC shortcut name
     *
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Get the path to FileStorage
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get the persistant storage backing instance.
     *
     * @return mixed
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Get the rate instance.
     *
     * @return \bandwidthThrottle\tokenBucket\Rate
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Get the TokenBucket instance.
     *
     * @return \bandwidthThrottle\tokenBucket\TokenBucket
     */
    public function getBucket()
    {
        return $this->bucket;
    }

    /**
     * Get the (optional) BlockingConsumer instance.
     *
     * @return \bandwidthThrottle\tokenBucket\BlockingConsumer
     */
    public function getBlocker()
    {
        return $this->blocker;
    }

    /**
     * Set the driver IoC shortcut name.
     *
     * @param  string $driver
     * @return void
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * Set the path for FileStorage
     *
     * @param string $path
     * @return void
     */
    public function setPath($path)
    {
        //TODO: validate path
        $this->path = $path;
    }
    /**
     * Set the persistant storage backing instance.
     *
     * @param  mixed $storage
     * @return void
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
    }

    /**
     * Set the rate instance.
     *
     * @param \bandwidthThrottle\tokenBucket\Rate $rate
     * @return void
     */
    public function setRate($rate)
    {
        //TODO: validate rate
        $this->rate = $rate;
    }

    /**
     * Set the TokenBucket instance.
     *
     * @param \bandwidthThrottle\tokenBucket\TokenBucket $bucket
     * @return void
     */
    public function setBucket($bucket)
    {
        $this->bucket = $bucket;
    }

    /**
     * Set the (optional) BlockingConsumer instance.
     *
     * @param \bandwidthThrottle\tokenBucket\BlockingConsumer $blocker
     * @return void
     */
    public function setBlocker($blocker)
    {
        $this->blocker = $blocker;
    }
}
