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

use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

use Illuminate\Support\ServiceProvider;

use bandwidthThrottle\tokenBucket\Rate;
use bandwidthThrottle\tokenBucket\TokenBucket;
use bandwidthThrottle\tokenBucket\BlockingConsumer;
use bandwidthThrottle\tokenBucket\storage\FileStorage;

use Projectmentor\Quota\Factories\RateFactory;
use Projectmentor\Quota\Factories\FileStorageFactory;
use Projectmentor\Quota\Factories\TokenBucketFactory;
use Projectmentor\Quota\Factories\BlockingConsumerFactory;

use Projectmentor\Quota\Stubs\RateData;
use Projectmentor\Quota\Stubs\FileStorageData;
use Projectmentor\Quota\Stubs\TokenBucketData;
use Projectmentor\Quota\Stubs\BlockingConsumerData;

use Projectmentor\Quota\Console\Commands\ResetQuotaLog;
use Projectmentor\Quota\Console\Commands\TruncateQuotaLog;

/**
 * This is the Quota service provider.
 *
 * "There is no need to bind classes into the container
 * if they do not depend on any interfaces"
 * - @See https://laravel.com/docs/5.2/container#binding
 *
 * Hence the base classes we are exposing from the library
 * Are not going to be registered here.
 *
 * We will register our factory classes and the Quota class
 * which will be used both as a `manager` and as a concrete
 * instance by leveraging  call_user_func_array(). In this
 * manner we will be able to access Quota as a singleton,
 * via a Facade, and it will maintain an array of rate-limiters
 * which we can retrieve when needed.
 * @See Projectmentor\Quota\Quota.php
 *
 *
 * @author David Faith <david@projectmentor.org>
 */
class QuotaServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * NOTE: Since we are also configuring at boot time
     * we can not defer. 
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ResetQuotaLog::class,
                TruncateQuotaLog::class,
            ]);
        } 
    }

     /**
     * Register a database migration path.
     *
     * NOTE: This function is already part of 5.4
     * it is backported here for 5.2
     *
     * @param  array|string  $paths
     * @return void
     */
    protected function loadMigrationsFrom($paths)
    {
        $this->app->afterResolving('migrator', function ($migrator) use ($paths) {
            foreach ((array) $paths as $path) {
                $migrator->path($path);
            }
        });
    } 

    /**
     * Setup the config
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../config/quota.php');

        if ($this->app instanceof LaravelApplication &&
            $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('quota.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('quota');
        }

        $this->mergeConfigFrom($source, 'quota');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->bindInterfaces();
        $this->registerFactories();
        $this->registerClasses();
    }

    /**
     * Bind interfaces to implementations.
     * @return void
     */
    public function bindInterfaces()
    {
        $this->app->when(RateFactory::class)
          ->needs('Projectmentor\Quota\Contracts\PayloadInterface')
          ->give(RateData::class);

        $this->app->when(FileStorageFactory::class)
          ->needs('Projectmentor\Quota\Contracts\PayloadInterface')
          ->give(FileStorageData::class);

        $this->app->when(TokenBucketFactory::class)
          ->needs('Projectmentor\Quota\Contracts\PayloadInterface')
          ->give(TokenBucketData::class);

        $this->app->when(BlockingConsumerFactory::class)
          ->needs('Projectmentor\Quota\Contracts\PayloadInterface')
          ->give(BlockingConsumerData::class);
    }

    /**
     * Register the factory classes.
     * @return void
     */
    protected function registerFactories()
    {
        $this->app->singleton('quota.factory.rate', function ($app) {
            return new RateFactory;
        });

        $this->app->singleton('quota.factory.storage.file', function ($app) {
            return new FileStorageFactory;
        });

        $this->app->singleton('quota.factory.tokenbucket', function ($app) {
            return new TokenBucketFactory;
        });

        $this->app->singleton('quota.factory.blockingconsumer', function ($app) {
            return new BlockingConsumerFactory;
        });

        $this->app->alias('quota.factory.rate', RateFactory::class);
        $this->app->alias('quota.factory.rate', FactoryInterface::class);

        $this->app->alias('quota.factory.storage.file', FileStorageFactory::class);
        $this->app->alias('quota.factory.storage.file', FactoryInterface::class);

        $this->app->alias('quota.factory.tokenbucket', TokenBucketFactory::class);
        $this->app->alias('quota.factory.tokenbucket', FactoryInterface::class);

        $this->app->alias('quota.factory.blockingconsumer', BlockingConsumerFactory::class);
        $this->app->alias('quota.factory.blockingconsumer', FactoryInterface::class);
    }

    /**
     * Register application classes.
     *
     * TODO: REFACTOR Binding with parameters is not
     * Best practice. 
     * @See https://github.com/laravel/internals/issues/391
     *
     * @return void
     */
    protected function registerClasses()
    {
        $this->app->bind('quota.storage.file', function($app, $params){
            return new FileStorage($params['path']);
        });

        $this->app->bind('quota.rate', function($app, $params){
            return new Rate($params['limit'], $params['period']);
        });

        $this->app->bind('quota.bucket', function($app, $params){
            return new TokenBucket($params['capacity'], $params['rate'], $params['storage']);
        });

        $this->app->bind('quota.blocker', function($app, $params){
            return new BlockingConsumer($params['bucket']);
        });

        $this->app->alias('quota.storage.file', FileStorage::class);
        $this->app->alias('quota.storage.file', StorageInterface::class);

        $this->app->alias('quota.rate', Rate::class);

        $this->app->alias('quota.bucket', Tokenbucket::class);

        $this->app->alias('quota.blocker', BlockingConsumer::class);
    }

    /**
     * Get the services provided by this provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'quota.factory.rate',
            'quota.factory.storage.file',
            'quota.factory.tokenbucket',
            'quota.factory.blockingconsumer',
            'quota.storage.file',
            'quota.rate',
            'quota.bucket',
            'quota.blocker'
        ];
    }
}
