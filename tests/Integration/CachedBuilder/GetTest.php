<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class GetTest extends IntegrationTestCase
{
    public function testGetModelResultsCreatesCache()
    {
        $authors = (new Author)->with('books', 'profile')
            ->get();
        $key = sha1('genealabs:laravel-model-caching:testing::memory::test-prefix:authors:genealabslaravelmodelcachingtestsfixturesauthor-testing::memory::books-testing::memory::profile');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::test-prefix:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing::memory::test-prefix:genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:testing::memory::test-prefix:genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = $this->cache()->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)->with('books', 'profile')
            ->get();

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testAccessingGetResultsViaArrayIndexDoesNotError()
    {
        $author = (new Author)
            ->where('id', 1)
            ->get()[0];
        $cachedAuthor = (new Author)
            ->where('id', 1)
            ->get()[0];
        $uncachedAuthor = (new UncachedAuthor)
            ->where('id', 1)
            ->get()[0];

        $this->assertEquals(1, $author->id);
        $this->assertEquals($author, $cachedAuthor);
        $this->assertEquals($author->toArray(), $uncachedAuthor->toArray());
    }

    public function testGetWithFieldArrayCachesResults()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::test-prefix:authors:genealabslaravelmodelcachingtestsfixturesauthor_id_name');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::test-prefix:genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $authors = (new Author)
            ->get(["id", "name"]);
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->get();

        $this->assertEquals($liveResults->pluck("id"), $authors->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
    }
}
