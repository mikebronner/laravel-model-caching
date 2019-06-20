<?php namespace GeneaLabs\LaravelModelCaching;

use Illuminate\Container\Container;

class Helper
{
    public function runDisabled(callable $closure)
    {
        $originalSetting = Container::getInstance()
            ->make("config")
            ->get('laravel-model-caching.disabled');

        Container::getInstance()
            ->make("config")
            ->set(['laravel-model-caching.disabled' => true]);

        $result = $closure();

        Container::getInstance()
            ->make("config")
            ->set(['laravel-model-caching.disabled' => $originalSetting]);

        return $result;
    }
}
