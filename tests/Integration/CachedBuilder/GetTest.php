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

class GetTest extends IntegrationTestCase
{
    

    public function testGetModelResultsCreatesCache()
    {
        $authors = (new Author)->with('books', 'profile')
            ->get();
        $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor-books-profile');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesprofile',
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
        $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor_id_name');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor',
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
