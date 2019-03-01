<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Profile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedProfile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPublisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedStore;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Http\Resources\Author as AuthorResource;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

class FindTest extends IntegrationTestCase
{
    public function testFindModelResultsCreatesCache()
    {
        $author = collect()->push((new Author)->find(1));
        $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor_1');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResults = collect()->push($this->cache()->tags($tags)
            ->get($key));
        $liveResults = collect()->push((new UncachedAuthor)->find(1));

        $this->assertEmpty($author->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testFindMultipleModelResultsCreatesCache()
    {
        $authors = (new Author)->find([1, 2, 3]);
        $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor-find_list_1_2_3');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor',
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
}
