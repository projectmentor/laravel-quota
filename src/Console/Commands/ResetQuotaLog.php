<?php

/**
 * This file is part of laravel-quota
 *
 * (c) David Faith <david@projectmentor.org>
 *
 * Full copyright and license information is available
 * in the LICENSE file distributed with this source code.
 */

namespace Projectmentor\Quota\Console\Commands;

use Illuminate\Console\Command;

use DB;

class ResetQuotaLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quota:reset {date} {connection} {hits=0} {misses=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the quotalog counters for a date and connection.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        switch(DB::connection()->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME))
        {
        case 'mysql':

            $sql = 'INSERT INTO quotalog' .
                ' (date, connection, hits, misses, created_at, updated_at)' .
                ' VALUES' .
                ' ( :date, :connection, :hits, :misses, :created_at, :updated_at)' .
                ' ON DUPLICATE KEY UPDATE' .
                ' hits = VALUES(hits),' .
                ' misses = VALUES(misses),' .
                ' updated_at = VALUES(updated_at)';

            $this->doUpsertStatement($sql);

            break;
        case 'sqlite':

            $date = $this->argument('date');
            $connection = $this->argument('connection');

            $sql = 'UPDATE quotalog' .
                ' SET `date` = :date' .
                ', `connection` = :connection' .
                ', `hits` = :hits' . 
                ', `misses` = :misses' .
                ', `updated_at` = :updated_at' .
                ' WHERE `date` = ' . '\''. $date . '\'' .
                ' AND `connection` = ' . '\'' . $connection . '\'';

            $this->doUpdateStatement($sql);

            $changes = DB::select('SELECT changes()');
            if(! $this->sqliteUpdateDidChange($changes))
            {
                $sql = 'INSERT INTO quotalog' .
                  ' (' .
                  ' `date`' .
                  ', `connection`' .
                  ', `hits`' .
                  ', `misses`' .
                  ', `created_at`' .
                  ', `updated_at`' .
                  ')' . 
                  ' VALUES' .
                  ' (' . 
                  ' :date' .
                  ', :connection' .
                  ', :hits' .
                  ', :misses' .
                  ', :created_at' .  
                  ', :updated_at' .
                  ')';

                $this->doUpsertStatement($sql);
            }
            break;

        default:
            throw new \ErrorException(
                __CLASS__.'::'.__FUNCTION__.
                ' Driver: ' . $driver . ' not supported.');
        }
    }

    /**
     * Helper. avoid duplicate code
     * Run sqlite3 update statement.
     * which must have same number of params
     * as sql bindings.
     *
     * @param string $sql
     * @return void
     */
    protected function doUpdateStatement($sql)
    {
        $now = \Carbon\Carbon::now()->toDateTimeString();

        DB::statement($sql, [
            'date' => $this->argument('date'),
            'connection' => $this->argument('connection'),
            'hits' =>  $this->argument('hits'),
            'misses' =>  $this->argument('misses'),
            'updated_at' => $now
        ]);
    }

    /**
     * Helper. avoid duplicate code
     * Run insert or update statement.
     * 
     * @param string $sql
     * @return void
     */
    protected function doUpsertStatement($sql)
    {
        $now = \Carbon\Carbon::now()->toDateTimeString();

        DB::statement($sql, [
            'date' => $this->argument('date'),
            'connection' => $this->argument('connection'),
            'hits' =>  $this->argument('hits'),
            'misses' =>  $this->argument('misses'),
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }

    /**
     * Get the status of a sqlite3 UPDATE operation
     * from native stdClass
     *
     * 
     * @param array $changes e.g:
     *     [ 0 => { "changes()": 1 }]
     * @return boolean
     */
    protected function sqliteUpdateDidChange(array $changes)
    {
        $status = 'changes()';
        return $changes[0]->$status;
    }
}
