<?php namespace GeneaLabs\LaravelModelCaching;

class Helper
{
    public function runDisabled(callable $closure)
    {
        $originalSetting = config('laravel-model-caching.disabled');
        config(['laravel-model-caching.disabled' => true]);

        $result = $closure();

        config(['laravel-model-caching.disabled' => $originalSetting]);

        return $result;
    }
}
