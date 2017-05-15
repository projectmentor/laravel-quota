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

use bandwidthThrottle\tokenBucket\BlockingConsumer;
use Projectmentor\Quota\Contracts\FactoryInterface;
use Projectmentor\Quota\Contracts\PayloadInterface;

/**
 * This is the blockingConsumer factory class.
 *
 * @author David Faith <david@projectmentor.org>
 */
class BlockingConsumerFactory implements FactoryInterface
{
    /**
     * Make a new BlockingConsumer instance.
     *
     * @param \Projectmentor\Quota\Stubs\PayloadInterface $data
     * @return \Projectmentor\Quota\BlockingConsumer
     */
    public function make(PayloadInterface $data)
    {
        return new BlockingConsumer($data->getBucket());
    }
}
