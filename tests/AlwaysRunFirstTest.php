<?php namespace GeneaLabs\LaravelModelCaching\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class AlwaysRunFirstTest extends BaseTestCase
{
    use EnvironmentSetup;

    public function setUp() : void
    {
        parent::setUp();

        $this->app['config']->set('database.default', 'baseline');
        $this->app['config']->set('database.connections.baseline', [
            'driver' => 'sqlite',
            "url" => null,
            'database' => __DIR__ . '/database/baseline.sqlite',
            'prefix' => '',
            "foreign_key_constraints" => false,
        ]);

        $this->createBaselineSqliteDatabase();

        $this->withFactories(__DIR__ . '/database/factories');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this
            ->artisan(
                'db:seed',
                [
                    '--class' => 'DatabaseSeeder',
                    '--database' => 'baseline',
                ]
            )
            ->run();
    }

    private function createBaselineSqliteDatabase()
    {
        shell_exec("cd " . __DIR__ . "/database && rm *.sqlite && touch baseline.sqlite");
    }

    /** @test */
    public function migrateAndInstallTheDatabase()
    {
        $this->assertTrue(true);
    }
}
