<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class LazyLoadTest extends IntegrationTestCase
{
    public function testLazyLoadingRelationshipQuery()
    {
        $bookId = (new Store)
            ->disableModelCaching()
            ->with("books")
            ->first()
            ->books
            ->first()
            ->id;
        $key = sha1("genealabs:laravel-model-caching:testing::memory::test-prefix:book-store:genealabslaravelmodelcachingcachedbelongstomany-book_store.book_id_=_{$bookId}");
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::test-prefix:genealabslaravelmodelcachingtestsfixturesstore',
        ];

        $stores = (new Book)
            ->find($bookId)
            ->stores;
        $cachedStores = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $uncachedBook = (new UncachedBook)
            ->find($bookId);
        $uncachedStores = $uncachedBook->stores;

        $this->assertEquals($uncachedStores->pluck("id"), $stores->pluck("id"));
        $this->assertEquals($uncachedStores->pluck("id"), $cachedStores->pluck("id"));
        $this->assertNotNull($cachedStores);
        $this->assertNotNull($uncachedStores);
    }
}
