<?php namespace GeneaLabs\LaravelModelCaching\Providers;

use GeneaLabs\LaravelModelCaching\Console\Commands\Clear;
use GeneaLabs\LaravelModelCaching\Helper;
use Illuminate\Support\ServiceProvider;

class Service extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $configPath = __DIR__ . '/../../config/laravel-model-caching.php';
        $this->mergeConfigFrom($configPath, 'laravel-model-caching');
        $this->commands(Clear::class);
    }

    public function register()
    {
        $this->app->bind("model-cache", Helper::class);
    }
}
