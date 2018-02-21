<?php namespace GeneaLabs\LaravelModelCaching\Tests;

use GeneaLabs\LaravelModelCaching\Providers\Service as LaravelModelCachingService;
use Orchestra\Database\ConsoleServiceProvider;

trait CreatesApplication
{
    public function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/database/factories');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelModelCachingService::class,
            ConsoleServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.redis.default', [
            'host' => env('REDIS_HOST', '192.168.10.10'),
        ]);
        $app['config']->set('database.redis.model-cache', [
            'host' => env('REDIS_HOST', '192.168.10.10'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 1,
        ]);
        $app['config']->set('cache.stores.model', [
            'driver' => 'redis',
            'connection' => 'model-cache',
        ]);
        $app['config']->set('laravel-model-caching.store', 'model');
    }
}
