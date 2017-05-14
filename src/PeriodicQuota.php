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

use DB;
use Carbon\Carbon;

/**
 * This is the periodic quota class.
 * It is used to limit access to a resource
 * over a defined period of time.
 *
 * NOTE: Currently only supports daily quota
 * period. i.e. $this->period = 'day'
 *
 * TODO: REFACTOR for different period types.
 * @See constants in bandwidthThrottle\tokenBucket\Rate.php
 * for allowed period types.
 */

class PeriodicQuota extends Quota 
{
    /**
     * The timezone authority.
     * Used to roll the log records in the database.
     *
     * @var string
     */
    protected $timezone;

    /**
     * The database table name
     *
     * @var string
     */
    protected $log_table;

    /**
     *  Construct instance.
     *
     *  @param string $connection
     *  @return void
     */
    public function __construct($connection)
    {
        parent::__construct($connection);

        $this->connection = $connection;
        $this->index = 'quota.connections.' . $connection;

        $this->timezone = config($this->index . '.timezone');
        if(is_null($this->timezone))
            $this->timezone = config('quota.default_timezone');

        $this->log_table = config($this->index . '.log_table');

        //Bootstrap log if record does not exist.
        $dtz = new \DateTimeZone($this->timezone);
        $now = new \DateTime(date("Y-m-d"), $dtz);
        $date = $now->format("Y-m-d");
        $log_records = DB::select(
            'SELECT * FROM' .
            ' ' .  $this->log_table .
            ' WHERE date = ' . '\'' . $date . '\'' .
            '  AND connection = ' . '\'' . $connection . '\''
        );

        if(empty($log_records))
            \Artisan::call('quota:reset', [
                'date' => $date,
                'connection' =>  $connection
            ]);
    }

    public function enforce()
    {
        return $this->consume();
    }

    /**
     * Helper.
     *
     * TODO: make static?
     *
     * @param PeriodicQuota $quota
     * @return string
     */
    public function dateInTimezone()
    {
        $dtz = new \DateTimeZone($this->getTimezone());
        $now = new \DateTime(date("Y-m-d"), $dtz);
        return($now->format("Y-m-d"));
    }

    /**
     * Hiiiiit iiiiiit ! :)
     * Record a hit in the log table.
     *
     * @param int $hits so far
     * @return void
     */
    public function hit($hits)
    {
        $date = $this->dateInTimezone();
        $hits = $hits + 1;

        DB::statement(
            'UPDATE ' . $this->log_table .
            ' SET hits = ' . $hits .
            ', updated_at = ' . '\'' . Carbon::now()->toDateTimeString() . '\'' .
            ' WHERE date = ' . '\'' . $date . '\'' .
            ' AND connection = ' . '\'' . $this->connection . '\''
        );
    }

    /**
     * Record a miss in the log table.
     *
     * @param int $misses so far
     * @return void
     */
    public function miss($misses)
    {
        $date = $this->dateInTimezone();
        $misses = $misses + 1;

        DB::statement(
            'UPDATE ' . $this->log_table .
            ' SET misses = ' . $misses .
            ', updated_at = ' . '\'' . Carbon::now()->toDateTimeString() . '\'' .
            ' WHERE date = ' . '\'' . $date . '\'' .
            ' AND connection = ' . '\'' . $this->connection . '\''
        );
    }

    /**
     * Attempt to... HIT dat a$$!  :)
     *
     * @param integer $tokens future use.
     * @return boolean true on success
     *
     * @throws ErrorException
     */
    public function consume($tokens = 1)
    {
        $date = $this->dateInTimezone();

        $stats = $this->getStats($date);
        $hits = (integer) $stats->hits;

        if($this->limit < ($hits + 1))
        {
           $this->miss($stats->misses);
           throw new \ErrorException(
               __CLASS__ . '::' . __FUNCTION__ .
               ' Overquota. Exceeded daily limit: ' . $this->limit);
        }
        else
        {
            $this->hit($stats->hits);
            $result = true;
        }
         return isset($result);
    }

    /**
     * Get statistics from log table.
     *
     * TODO: REFACTOR rename `date` to `period`
     * to reflect ability to track different periodic
     * limits.
     *
     * @param string $date
     * @return stdObject
     * @throws Exception
     */
    public function getStats($date)
    {
        return DB::table('quotalog')
           ->where('date', $date)
           ->where('connection', $this->connection)
           ->first();
    }

    /**
     * Get the timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Get the log table name
     *
     * @return string
     */
    public function getLogTable()
    {
        return $this->log_table;
    }

    /**
     * Set the timezone authority
     *
     * @param string
     * @return void
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * Set the log_table name
     *
     * @param string
     * @return void
     */
    public function setLogTable($log_table)
    {
        $this->log_table = $log_table;
    }
}
