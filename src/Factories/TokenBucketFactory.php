<?php

/**
 * This file is part of laravel-quota
 *
 * (c) David Faith <david@projectmentor.org>
 *
 * Full copyright and license information is available
 * in the LICENSE file distributed with this source code.
 */

namespace Projectmentor\Quota\Factories;

use bandwidthThrottle\tokenBucket\TokenBucket;
use Projectmentor\Quota\Contracts\FactoryInterface;
use Projectmentor\Quota\Contracts\PayloadInterface;

/**
 * This is the token-bucket TokenBucket factory class.
 *
 * @author David Faith <david@projectmentor.org>
 */
class TokenBucketFactory implements FactoryInterface
{
    /**
     * Make a new TokenBucket instance.
     *
     * @param \Projectmentor\Quota\TokenBucketData $data
     * @return \bandwidthThrottle\tokenBucket\TokenBucket
     */
    public function make(PayloadInterface $data)
    {
        return new TokenBucket(
            $data->getCapacity(),
            $data->getRate(),
            $data->getStorage()
        );
    }
}
