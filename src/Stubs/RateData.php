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
 * This is the rate data class.
 *
 * It stubs constructor parameters for:
 * bandwithThottle\tokenBucket\Rate
 *
 * Initial properties for a new Rate instance via
 * Projectmentor\Quota\Factories\FactoryInterface
 *
 * @author David Faith <david@projectmentor.org>
 */
class RateData implements PayloadInterface
{
    /**
     * The rate limit
     * i.e. capacity of the token-bucket
     * @var string
     */
    protected $limit;

    /**
     * The time period constant from Rate
     * @var string
     */
    protected $period;

    /**
     * Initialize $this
     * @param int $limit capacity
     * @param string $period Rate::<CONSTANT>
     * @return void
     */
    public function __construct($limit, $period)
    {
        $this->limit = $limit;
        $this->period = $period;
    }

    /**
     * Get the token bucket capacity
     * i.e: the maximum number of
     * tokens available at any time.
     * @return
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Get the constant name for the
     * time period between
     * bucket refill.
     * e.g: 'SECOND' | 'DAY' ...
     *
     * @return string
     */
    public function getPeriod()
    {
        return $this->period;
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
