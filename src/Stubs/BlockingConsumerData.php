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
use Projectmentor\Quota\Contracts\FactoryInterface;

/**
 * This is the BlockingConsumer data class.
 *
 * It stubs constructor parameters for:
 * bandwithThottle\tokenBucket\BlockingConsumer
 *
 * Initial properties for a new BlockingConsumer instance via
 * \Projectmentor\Quota\Factories\FactoryInterface
 *
 * @author David Faith <david@projectmentor.org>
 */
class BlockingConsumerData implements PayloadInterface
{
    /**
     * The TokenBucket.
     * @var bandwithThottle\tokenBucket\TokenBucket
     */
    protected $bucket;

    /**
     * Initialize $this
     * @param bandwithThottle\tokenBucket\TokenBucket
     * @return void
     */
    public function __construct($bucket)
    {
        $this->bucket = $bucket;
    }

    /**
     * Get the bucket instance.
     * @return bandwithThottle\tokenBucket\TokenBucket
     */
    public function getBucket()
    {
        return $this->bucket;
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
