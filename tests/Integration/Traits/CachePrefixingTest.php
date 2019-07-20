<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\Traits;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class CachePrefixingTest extends IntegrationTestCase
{
    public function testDatabaseKeyingEnabled()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null-first");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $author = (new Author)
            ->first();
        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->first();

        $this->assertEquals($liveResults->pluck("id"), $author->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
        $this->assertNotEmpty($author);
        $this->assertNotEmpty($cachedResults);
        $this->assertNotEmpty($liveResults);
    }

    public function testDatabaseKeyingDisabled()
    {
        config(["laravel-model-caching.use-database-keying" => false]);
        $key = sha1("genealabs:laravel-model-caching:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null-first");
        $tags = ["genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor"];

        $author = (new Author)
            ->first();
        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->first();

        $this->assertEquals($liveResults->pluck("id"), $author->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
        $this->assertNotEmpty($author);
        $this->assertNotEmpty($cachedResults);
        $this->assertNotEmpty($liveResults);
    }
}
