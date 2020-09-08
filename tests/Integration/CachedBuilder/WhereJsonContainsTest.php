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
        $app['config']->set('database.connections.pgsql.host', env("PGSQL_HOST", "127.0.0.1"));
        $app['config']->set('database.connections.pgsql.database', env("PGSQL_DATABASE", "testing"));
        $app['config']->set('database.connections.pgsql.username', env("PGSQL_USERNAME", "homestead"));
        $app['config']->set('database.connections.pgsql.password', env("PGSQL_PASSWORD", "secret"));
    }

    public function setUp() : void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        factory(Author::class, 10)->create();
    }

    public function testWithInUsingCollectionQuery()
    {
        $key = sha1("genealabs:laravel-model-caching:pgsql:testing:authors:genealabslaravelmodelcachingtestsfixturesauthor-finances->total_jsoncontains_5000-authors.deleted_at_null");
        $tags = [
            'genealabs:laravel-model-caching:pgsql:testing:genealabslaravelmodelcachingtestsfixturesauthor',
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

        $this->assertCount(10, $cachedResults);
        $this->assertCount(10, $liveResults);
        $this->assertEquals($liveResults->pluck("id"), $authors->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
    }

    public function testWithInUsingCollectionQueryWithArrayValues()
    {
        $key = sha1("genealabs:laravel-model-caching:pgsql:testing:authors:genealabslaravelmodelcachingtestsfixturesauthor-finances->tags_jsoncontains_[\"foo\",\"bar\"]-authors.deleted_at_null");
        $tags = [
            'genealabs:laravel-model-caching:pgsql:testing:genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $authors = (new Author)
            ->whereJsonContains("finances->tags", ['foo', 'bar'])
            ->get();
        $liveResults = (new UncachedAuthor)
            ->whereJsonContains("finances->tags", ['foo', 'bar'])
            ->get();

        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];

        $this->assertCount(10, $liveResults);
        $this->assertCount(10, $cachedResults);
        $this->assertEquals($liveResults->pluck("id"), $authors->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
    }
}
