<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class DisabledCachedModelTest extends IntegrationTestCase
{
    public function testCacheCanBeDisabledOnModel()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor"];
        $authors = (new Author)
            ->disableCache()
            ->get();

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)
            ->get();

        $this->assertEmpty($liveResults->diffAssoc($authors));
        $this->assertNull($cachedResults);
    }

    public function testCacheCanBeDisabledOnQuery()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-testing:{$this->testingSqlitePath}testing.sqlite:books");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors",
        ];
        $authors = (new Author)
            ->with('books')
            ->disableCache()
            ->get()
            ->keyBy("id");

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)
            ->with('books')
            ->get()
            ->keyBy("id");

        $this->assertNull($cachedResults);
        $this->assertEmpty($liveResults->diffKeys($authors));
    }
}
