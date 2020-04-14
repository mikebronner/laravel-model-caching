<?php namespace GeneaLabs\LaravelModelCaching\Tests;

use GeneaLabs\LaravelModelCaching\Providers\Service as LaravelModelCachingService;

trait CreatesApplication
{
    use EnvironmentSetup;
    use MigrateBaselineDatabase;

    protected $cache;
    protected $testingSqlitePath;

    protected function cache()
    {
        $cache = app('cache');

        if (config('laravel-model-caching.store')) {
            $cache = $cache->store(config('laravel-model-caching.store'));
        }

        return $cache;
    }

    public function setUp() : void
    {
        parent::setUp();
        $this->setUpBaseLineSqlLiteDatabase();

        $databasePath = __DIR__ . "/database";
        $this->testingSqlitePath = "{$databasePath}/";
        $baselinePath = "{$databasePath}/baseline.sqlite";
        $testingPath = "{$databasePath}/testing.sqlite";

        ! file_exists($testingPath) ?: unlink($testingPath);
        copy($baselinePath, $testingPath);

        require(__DIR__ . '/routes/web.php');

        $this->withFactories(__DIR__ . '/database/factories');
        view()->addLocation(__DIR__ . '/resources/views', 'laravel-model-caching');

        $this->cache = app('cache')
            ->store(config('laravel-model-caching.store'));

        $this->cache()->flush();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelModelCachingService::class,
        ];
    }
}
