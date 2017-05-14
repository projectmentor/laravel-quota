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

use bandwidthThrottle\tokenBucket\Rate;
use Projectmentor\Quota\Contracts\Quota as QuotaInterface;

/**
 * This is the Quota class.
 *
 * NOTE: While currently implemented as a
 * concrete class for testing. This class
 * should be used only for inheritance.
 */
class Quota implements QuotaInterface
{
    /**
     * The configured connection name.
     *
     * @var string
     */
    protected $connection;

    /**
     * The configured connection index.
     *
     * @var string
     */
    protected $index;

    /**
     * The quota limit
     *
     * @var int
     */
    protected $limit;

    /**
     * The period to limit
     *
     * @var string
     */
    protected $period;

    /**
     *  Construct instance.
     *
     *  @param string $connection
     *  @return void
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->index = 'quota.connections.' . $connection;

        $this->limit = config($this->index . '.limit');

        $period = config($this->index . '.period');
        $this->setPeriod($period);
    }

    /**
     * Get a list of valid periods.
     * @See  bandwidthThrottle\tokenBucket\Rate::CONSTANTS
     *
     * @return array
     */
    public function validPeriods()
    {
        $r = new \ReflectionClass(Rate::class);
        //print_r($r->getConstants());
        return $r->getConstants();
    }

    /**
     * Validate period against Rate::CONSTANT values
     *
     * @param string $period one of:
     *        bandwidthThrottle\tokenBucket\Rate::CONSTANTS
     * @return boolean true on success
     */
    public function validatePeriod($period)
    {
        $result = false;
        $constants = $this->validPeriods();
        $values = array_values($constants);
        if(in_array($period, $values))
            $result = true;
        return $result;
    }

   /**
    * Public interface.
    *
    * NOTE: this class and method are really abstract,
    * but we leave it instantiable for easier testing. 
    */ 
    public function enforce()
    {
        //Override this in descendants.
        throw new \Exception('Not implemented here.');
    }

    /**
     * Get the connection name.
     *
     * @return string
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the index for the connection
     *
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Get the limit
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Get the period.
     *
     * @return string \bandwidthThrottle\tokenBucket\Rate::CONSTANT
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Set the connection name.
     *
     * @param string $connection
     * @return void
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Set the index
     *
     * @param string $index
     * @return void
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * Set the limit
     *
     * @param  int $limit
     * @return void
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * Set the period.
     *
     * @param string \bandwidthThrottle\tokenBucket\Rate::CONSTANT
     * @return void
     * @throws InvalidArgumentException
     * 
     */
    public function setPeriod($period)
    {
        if(! $this->validatePeriod($period))
        {
            $expected = $this->validPeriods();

            throw new \InvalidArgumentException(
                __CLASS__.'::'.__FUNCTION__.
                ' Invalid period: ' . $period . 
                ' in connection configuration: ' . $this->connection .
                ' expected one of: ' . PHP_EOL . print_r($expected,1));
        }

        $this->period = $period;
    }
}
