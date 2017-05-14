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
 * This is the filestorage data class.
 *
 * It stubs constructor parameters for:
 * bandwithThottle\tokenBucket\storage\FileStorage
 *
 * Initial properties for a new FileStorage instance via
 * \Projectmentor\Quota\Factories\FactoryInterface
 *
 * @author David Faith <david@projectmentor.org>
 */
class FileStorageData implements PayloadInterface
{
    /**
     * The path to persistant storage
     * @var string
     */
    protected $path;

    /**
     * Initialize $this
     * @param string $path to persistant storage
     * @return void
     */
    public function __construct($path)
    {
        //TODO: validate
        $this->path = $path;
    }

    /**
     * Get the path attribute.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
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
