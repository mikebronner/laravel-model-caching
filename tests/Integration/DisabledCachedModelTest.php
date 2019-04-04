<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration;

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
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DisabledCachedModelTest extends IntegrationTestCase
{
    public function testCacheCanBeDisabledOnModel()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor'];
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
        $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor-testing::memory::books');
        $tags = [
            "genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor",
            "genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook",
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
