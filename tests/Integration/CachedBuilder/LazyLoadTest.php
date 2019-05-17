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

class LazyLoadTest extends IntegrationTestCase
{
     public function testLazyLoadingRelationshipQuery()
     {
        $key = sha1('genealabs:laravel-model-caching:mysql:eloquent:book-store:genealabslaravelmodelcachingcachebelongstomany-book_store.book_id_=_15');
        $tags = [
            'genealabs:laravel-model-caching:mysql:eloquent:genealabslaravelmodelcachingtestsfixturesstore',
        ];
        $book = (new Book)::find(15);
        $stores = $book->stores;
        $cachedStores = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $uncachedBook = (new UncachedBook)->find(15);
        $uncachedStores = $uncachedBook->stores;

        $this->assertEquals($cachedStores->pluck("id"), $uncachedStores->pluck("id"));
     }
}
