<?php

/**
 * This file is part of laravel-quota
 *
 * (c) David Faith <david@projectmentor.org>
 *
 * Full copyright and license information is available
 * in the LICENSE file distributed with this source code.
 */

namespace Projectmentor\Quota\Tests;

use Projectmentor\Quota\PeriodicQuota;
use GrahamCampbell\TestBench\AbstractPackageTestCase;
use Illuminate\Support\Facades\Facade;
//use Projectmentor\Quota\Helpers\MigrateTrait;
use Orchestra\Database\ConsoleServiceProvider;

use Projectmentor\Quota\QuotaServiceProvider;

//TODO: REFACTOR should extend QuotaTest

class PeriodicQuotaTest extends AbstractPackageTestCase
{

    //use MigrateTrait;
    //
    //NOTE: Can't use DatabaseTransactions trait
    //Avoid sqlite "Database Locked" error.
    //use DatabaseTransactions;

    protected $backup; //of config/quota.php

    //public function createApplication()
    //{
    //    $app = parent::createApplication();
    //    $app->register(QuotaServiceProvider::class);

    //    //resolves to: 'Orchestra\Testbench\Console\Kernel'
    //    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    //    return $app;
    //}
    

    protected function getPackageProviders($app)
    {
            return ['Projectmentor\Quota\QuotaServiceProvider'];
    }

    /**
     * Define environment setup.
     * Override Orchestra\TestBench
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }


    /**
     * Setup before each test.
     * Truncate the log_table.
     * Backup and override the quota.config file.
     */
    public function setUp()
    {
        parent::setUp();

        //$this->artisan('migrate', ['--database' => 'sqlite']);

        //$this->app['config']->set('database.default','sqlite');
        //$this->app['config']->set('database.connections.sqlite.database', ':memory:');

        //$this->migrate();

        $realpath = realpath(__DIR__.'/../migrations');
        dump($realpath);

        $this->loadMigrationsFrom([
            '--database' => 'sqlite',
            '--realpath' => $realpath
        ]);

        \DB::table('quotalog')->truncate();

        $this->backup = \Config::get ('quota.connections');

        $override = [ 
            'test' => [
                'limit' => 10,
                'period' => 'day',
                'log_table' => 'quotalog',
                'timezone' =>  'America/Los_Angeles'
            ],
        ];
        \Config::set('quota.connections', $override);
    }

    /**
     * Teardown after each test.
     * Restore the quota.config file.
     */
    public function tearDown()
    {
        \Config::set('quota.connections', $this->backup);
    }

    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->assertTrue(true);
    }
    /**
     * @test
     * @group classes
     * @group classes_quota
     * @group classes_quota_periodic
     */
    public function testConstructor()
    {
        $connection = 'test';
        $quota = new PeriodicQuota($connection);

        $config_handle = $quota->getConnection();
        $this->assertEquals($connection, $config_handle);

        $index = $quota->getIndex();
        $this->assertEquals('quota.connections.test', $index);

        $limit = $quota->getLimit();
        $this->assertEquals(10, $limit);

        $period = $quota->getPeriod();
        $this->assertEquals('day', $period);

        $timezone = $quota->getTimezone();
        $this->assertEquals('America/Los_Angeles', $timezone);

        $log_table = $quota->getLogTable();
        $this->assertEquals('quotalog', $log_table);

        $log_records = \DB::select(
            'SELECT * FROM' .
            ' ' .  $log_table .
            ' WHERE date = '  . '\'' . $quota->dateInTimezone() . '\'' .
            '  AND connection = ' . '\'' . $connection . '\''
        );

        $this->assertEquals(1, count($log_records));
        $this->assertEquals(0, $log_records[0]->hits);
        $this->assertEquals(0, $log_records[0]->misses);
    }

    /**
     * @test
     * @group classes
     * @group classes_quota
     * @group classes_quota_periodic
     */
    public function testGetStats()
    {
        $quota = new PeriodicQuota('test');

        $stats = $quota->getStats($quota->dateInTimezone());
        $this->assertEquals(0, $stats->hits);
        $this->assertEquals(0, $stats->misses);

    } 

    /**
     * @test
     * @group classes
     * @group classes_quota
     * @group classes_quota_periodic
     */
    public function testHit()
    {
        $quota = new PeriodicQuota('test');
        $date = $quota->dateInTimezone($quota);
        $connection = $quota->getConnection();

        $log_records = \DB::select(
            'SELECT * FROM' .
            ' ' .  $quota->getLogTable() .
            ' WHERE date = ' . '\'' . $date . '\'' .
            '  AND connection = ' . '\'' . $connection . '\''
        );

        $hits = $log_records[0]->hits;
        $quota->hit($hits);
        
        //TODO: REFACTOR DUPLICATE CODE
        $results = \DB::select(
            'SELECT * FROM' .
            ' ' .  $quota->getLogTable() .
            ' WHERE date = ' . '\'' . $date . '\'' .
            '  AND connection = ' . '\'' . $connection . '\''
        );

        $this->assertEquals($hits + 1, $results[0]->hits);
    } 

    /**
     * @test
     * @group classes
     * @group classes_quota
     * @group classes_quota_periodic
     */
    public function testMiss()
    {
        $quota = new PeriodicQuota('test');
        $date = $quota->dateInTimezone($quota);
        $connection = $quota->getConnection();

        $log_records = \DB::select(
            'SELECT * FROM' .
            ' ' .  $quota->getLogTable() .
            ' WHERE date = ' . '\'' . $date . '\'' .
            '  AND connection = ' . '\'' . $connection . '\''
        );

        $misses = $log_records[0]->misses;
        $quota->miss($misses);
        
        //TODO: REFACTOR DUPLICATE CODE
        $results = \DB::select(
            'SELECT * FROM' .
            ' ' .  $quota->getLogTable() .
            ' WHERE date = ' . '\'' . $date . '\'' .
            '  AND connection = ' . '\'' . $connection . '\''
        );

        $this->assertEquals($misses + 1, $results[0]->misses);
    }

    /**
     * @test
     * @group classes
     * @group classes_quota
     * @group classes_quota_periodic
     */
    public function testConsume()
    {
        $quota = new PeriodicQuota('test');
        $date = $quota->dateInTimezone($quota);
        $connection = $quota->getConnection();

        $result = $quota->consume();
        $this->assertTrue($result);
        
        //TODO: REFACTOR DUPLICATE CODE
        $results = \DB::select(
            'SELECT * FROM' .
            ' ' .  $quota->getLogTable() .
            ' WHERE date = ' . '\'' . $date . '\'' .
            '  AND connection = ' . '\'' . $connection . '\''
        );

        $this->assertEquals(1, $results[0]->hits);
        $this->assertEquals(0, $results[0]->misses);
    }

    /**
     * @test
     * @group classes
     * @group classes_quota
     * @group classes_quota_periodic
     */
    public function testConsumeThrowsWhenOverquota()
    {
        $quota = new PeriodicQuota('test');
        $date = $quota->dateInTimezone($quota);
        $connection = $quota->getConnection();
        $limit = $quota->getLimit();

        $this->expectException(\ErrorException::class);
        for($i = 0; $i < limit + 1; $i++)
        {    
            $quota->consume();
        }
    }

    /**
     * @test
     * @group classes
     * @group classes_quota
     * @group classes_quota_periodic
     */
    public function testEnforce()
    {
        $quota = new PeriodicQuota('test');
        $date = $quota->dateInTimezone($quota);
        $connection = $quota->getConnection();

        $result = $quota->enforce();
        $this->assertTrue($result);
        
        //TODO: REFACTOR DUPLICATE CODE
        $results = \DB::select(
            'SELECT * FROM' .
            ' ' .  $quota->getLogTable() .
            ' WHERE date = ' . '\'' . $date . '\'' .
            '  AND connection = ' . '\'' . $connection . '\''
        );

        $this->assertEquals(1, $results[0]->hits);
        $this->assertEquals(0, $results[0]->misses);
    }

    /**
     * @test
     * @group classes
     * @group classes_quota
     * @group classes_quota_periodic
     */
    public function testEnforceThrowsWhenOverquota()
    {
        $quota = new PeriodicQuota('test');
        $date = $quota->dateInTimezone($quota);
        $connection = $quota->getConnection();
        $limit = $quota->getLimit();

        $this->expectException(\ErrorException::class);
        for($i = 0; $i < limit + 1; $i++)
        {    
            $quota->enforce();
        }
    }
}

