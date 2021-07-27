<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class FindOrFailTest extends IntegrationTestCase
{
    public function testFindOrFailCachesModels()
    {
        $author = (new Author)
            ->findOrFail(1);

        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null:http://localhost-find_1");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->findOrFail(1);

        $this->assertEquals($cachedResults->toArray(), $author->toArray());
        $this->assertEquals($liveResults->toArray(), $author->toArray());
    }

    public function testFindOrFailWithArrayReturnsResults()
    {
        $author = (new Author)->findOrFail([1, 2]);
        $uncachedAuthor = (new UncachedAuthor)->findOrFail([1, 2]);

        $this->assertEquals($uncachedAuthor->count(), $author->count());
        $this->assertEquals($uncachedAuthor->pluck("id"), $author->pluck("id"));
    }
}
