<?php namespace GeneaLabs\LaravelModelCaching\Tests;

trait EnvironmentSetup
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/database/testing.sqlite',
            'prefix' => '',
            "foreign_key_constraints" => false,
        ]);
        $app['config']->set('database.redis.cache', [
            'host' => env('REDIS_HOST', '192.168.10.10'),
        ]);
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
