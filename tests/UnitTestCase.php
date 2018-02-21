<?php namespace GeneaLabs\LaravelModelCaching\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class UnitTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function cache()
    {
        $cache = cache();

        if (config('laravel-model-caching.store')) {
            $cache = $cache->store(config('laravel-model-caching.store'));
        }

        return $cache;
    }
}
