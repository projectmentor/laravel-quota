<?php

/**
 * This file is part of laravel-quota
 *
 * (c) David Faith <david@projectmentor.org>
 *
 * Full copyright and license information is available
 * in the LICENSE file distributed with this source code.
 */

namespace Projectmentor\Quota\Stubs;

use Projectmentor\Quota\Contracts\PayloadInterface;

/**
 * This is the TokenBucket data class.
 *
 * It stubs constructor parameters for:
 * bandwithThottle\tokenBucket\TokenBucket
 *
 * Initial properties for a new TokenBucket instance via
 * \Projectmentor\Quota\Factories\FactoryInterface
 *
 * @author David Faith <david@projectmentor.org>
 */
class TokenBucketData implements PayloadInterface
{
    /**
     * The maximum token capacity
     * @var int
     */
    protected $capacity;

    /**
     * The bucket refresh rate
     * @var \bandwithThrottle\tokenBucket\Rate
     */
    protected $rate;

    /**
     * The storage instance
     * @var \bandwidthThrottle\tokenBucket\storage\FileStorage
     */
    protected $storage;

    /**
     * Initialize $this
     * @param int $capacity of the bucket
     * @param \bandwithThrottle\tokenBucket\Rate
     * @param \bandwidthThrottle\tokenBucket\storage\FileStorage
     * @return void
     */
    public function __construct($capacity, $rate, $storage)
    {
        $this->capacity = $capacity;
        $this->rate = $rate;
        $this->storage = $storage;
    }

    /**
     * Get the token bucket capacity
     * i.e: the maximum number of
     * tokens available at any time.
     *
     * @return int
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * Get Rate instance
     *
     * @return \bandwidthThrottle\tokenBucket\Rate
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Get the storage instance.
     *
     * @return \bandwithThrottle\tokenBucket\storage\FileStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Implements PayloadInterface
     * emit json payload.
     * @return string
     */
    public function toJson()
    {
        return 'Hi';
    }

    /**
     * Implements PayloadInterface
     * emit array payload.
     * @return string
     */
    public function toArray()
    {
        return ['Hi'];
    }
}
