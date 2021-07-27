<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class FindTest extends IntegrationTestCase
{
    public function testFindModelResultsCreatesCache()
    {
        $author = collect()->push((new Author)->find(1));
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor_1");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $cachedResults = collect()->push($this->cache()->tags($tags)
            ->get($key));
        $liveResults = collect()->push((new UncachedAuthor)->find(1));

        $this->assertEmpty($author->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testFindMultipleModelResultsCreatesCache()
    {
        $authors = (new Author)
            ->find([1, 2, 3]);
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null:http://localhost-find_list_1_2_3");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)["value"];
        $liveResults = (new UncachedAuthor)->find([1, 2, 3]);

        $this->assertEquals($authors->pluck("id"), $cachedResults->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
    }

    public function testSubsequentFindsReturnDifferentModels()
    {
        $author1 = (new Author)->find(1);
        $author2 = (new Author)->find(2);

        $this->assertNotEquals($author1, $author2);
        $this->assertEquals($author1->id, 1);
        $this->assertEquals($author2->id, 2);
    }

    public function testFindWithArrayReturnsResults()
    {
        $author = (new Author)->find([1, 2]);
        $uncachedAuthor = (new UncachedAuthor)->find([1, 2]);

        $this->assertEquals($uncachedAuthor->count(), $author->count());
        $this->assertEquals($uncachedAuthor->pluck("id"), $author->pluck("id"));
    }

    public function testFindWithSingleElementArrayDoesntConflictWithNormalFind()
    {
        $author1 = (new Author)
            ->find(1);
        $author2 = (new Author)
            ->find([1]);

        $this->assertNotEquals($author1, $author2);
        $this->assertIsIterable($author2);
        $this->assertEquals(Author::class, get_class($author1));
    }
}
