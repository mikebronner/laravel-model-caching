<?php

namespace GeneaLabs\LaravelModelCaching\Tests;

use Illuminate\Support\Facades\Artisan;

trait MigrateBaselineDatabase
{
    private static $baseLineDatabaseMigrated = false;

    public function setUpBaseLineSqlLiteDatabase()
    {
        if (self::$baseLineDatabaseMigrated) {
            return;
        }

        self::$baseLineDatabaseMigrated = true;
        $file = __DIR__ . '/database/baseline.sqlite';
        $this->app['config']->set('database.default', 'baseline');
        $this->app['config']->set('database.connections.baseline', [
            'driver' => 'sqlite',
            "url" => null,
            'database' => $file,
            'prefix' => '',
            "foreign_key_constraints" => false,
        ]);

        ! file_exists($file) ?: unlink($file);
        touch($file);

        $this->withFactories(__DIR__ . '/database/factories');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        Artisan::call('db:seed', [
            '--class' => 'DatabaseSeeder',
            '--database' => 'baseline',
        ]);

        // Reset default connection to testing
        $this->app['config']->set('database.default', 'testing');
    }
}
