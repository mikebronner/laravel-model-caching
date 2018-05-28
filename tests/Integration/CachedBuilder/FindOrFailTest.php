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

class FindOrFailTest extends IntegrationTestCase
{
    

    public function testFindOrFailCachesModels()
    {
        $author = (new Author)
            ->findOrFail(1);

        $key = sha1('genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor-find_1');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor',
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
