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

use bandwidthThrottle\tokenBucket\storage\FileStorage;

use Projectmentor\Quota\Contracts\FactoryInterface;
use Projectmentor\Quota\Contracts\PayloadInterface;

/**
 * This is the quota storage factory class.
 *
 * @author David Faith <david@projectmentor.org>
 */
class FileStorageFactory implements FactoryInterface
{
    /**
     * Make a new FileStorage instance.
     *
     * @param \Projectmentor\Quota\Stubs\PayloadInterface $data;
     * @return \Projectmentor\Quota\Storage\FileStorage
     */
    public function make(PayloadInterface $data)
    {
        return new FileStorage($data->getPath());
    }
}
