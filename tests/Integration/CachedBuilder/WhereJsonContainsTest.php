<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WhereJsonContainsTest extends IntegrationTestCase
{
    use RefreshDatabase;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.default', 'pgsql');
    }

    public function setUp() : void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    public function testWithInUsingCollectionQuery()
    {
        $key = sha1('genealabs:laravel-model-caching:pgsql:testing:test-prefix:authors:genealabslaravelmodelcachingtestsfixturesauthor-finances->total_jsoncontains_5000');
        $tags = [
            'genealabs:laravel-model-caching:pgsql:testing:test-prefix:genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $authors = (new Author)
            ->whereJsonContains("finances->total", 5000)
            ->get();
        $liveResults = (new UncachedAuthor)
            ->whereJsonContains("finances->total", 5000)
            ->get();

        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];

        $this->assertEquals($liveResults->pluck("id"), $authors->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
    }
}
