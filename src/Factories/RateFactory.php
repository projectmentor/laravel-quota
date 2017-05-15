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

use bandwidthThrottle\tokenBucket\Rate;
use Projectmentor\Quota\Contracts\FactoryInterface;
use Projectmentor\Quota\Contracts\PayloadInterface;

/**
 * This is the token-bucket Rate factory class.
 *
 * @author David Faith <david@projectmentor.org>
 */
class RateFactory implements FactoryInterface
{
    /**
     * Make a new rate instance.
     *
     * TODO: abstract the return value via contract
     *
     * @param \Projectmentor\Quota\Contracts\PayloadInterface $data
     * @return \bandwidthThrottle\tokenBucket\Rate
     */
    public function make(PayloadInterface $data)
    {
        return new Rate($data->getlimit(), $data->getPeriod());
    }

    /**
     * Retrieve value for constant defined in Rate::class.
     * Convienience method.
     *
     * @param string $key constant name
     * @return string constant value
     * @throws InvalidArgumentException
     */
    public function getConstant($key)
    {
        $constants = $this->getConstants();
        if (array_key_exists($key, $constants)) {
            return $constants[$key];
        } else {
            throw new \InvalidArgumentException(
                __CLASS__.'::'.__FUNCTION__.
                ' Invalid constant. Got: ' . $key .
                ' Expected: ' . print_r($constants, 1)
            );
        }
    }

    /**
     * Retrieve an array of constants defined in Rate::class.
     * Convienience method.
     *
     * @return array
     */
    public function getConstants()
    {
        $reflect = new \ReflectionClass(Rate::class);
        return $reflect->getConstants();
    }
}
