<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\BookWithUncachedStore;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class BelongsToManyTest extends IntegrationTestCase
{
    public function test_lazy_loading_relationship(): void
    {
        $bookId = (new Store)
            ->disableModelCaching()
            ->with('books')
            ->first()
            ->books
            ->first()
            ->id;
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:stores:genealabslaravelmodelcachingcachedbelongstomany-book_store.book_id_=_{$bookId}");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesstore",
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

        $this->assertEquals($uncachedStores->pluck('id'), $stores->pluck('id'));
        $this->assertEquals($uncachedStores->pluck('id'), $cachedStores->pluck('id'));
        $this->assertNotNull($cachedStores);
        $this->assertNotNull($uncachedStores);
    }

    public function test_invalidating_cache_when_attaching()
    {
        $bookId = (new Store)
            ->disableModelCaching()
            ->with('books')
            ->first()
            ->books
            ->first()
            ->id;
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:stores:genealabslaravelmodelcachingtestsfixturesstore-testing:{$this->testingSqlitePath}testing.sqlite:books-first");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesstore",
        ];
        $newStore = Store::factory()->create();
        $result = (new Book)
            ->find($bookId)
            ->stores;

        (new Book)
            ->find($bookId)
            ->stores()
            ->attach($newStore->id);
        $cachedResult = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value']
            ?? null;

        $this->assertNotEmpty($result);
        $this->assertNull($cachedResult);
    }

    public function test_invalidating_cache_when_detaching()
    {
        $bookId = (new Store)
            ->disableModelCaching()
            ->with('books')
            ->first()
            ->books
            ->first()
            ->id;
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:book-store:genealabslaravelmodelcachingcachedbelongstomany-book_store.book_id_=_{$bookId}");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesstore",
        ];
        $result = (new Book)
            ->find($bookId)
            ->stores;

        (new Book)
            ->find($bookId)
            ->stores()
            ->detach($result->first()->id);
        $cachedResult = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value']
            ?? null;

        $this->assertNotEmpty($result);
        $this->assertNull($cachedResult);
    }

    public function test_invalidating_cache_when_updating()
    {
        $bookId = (new Store)
            ->disableModelCaching()
            ->with('books')
            ->first()
            ->books
            ->first()
            ->id;
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:book-store:genealabslaravelmodelcachingcachedbelongstomany-book_store.book_id_=_{$bookId}");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesstore",
        ];
        $result = (new Book)
            ->find($bookId)
            ->stores;

        $store = $result->first();
        $store->address = 'test address';
        $store->save();
        $cachedResult = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value']
            ?? null;

        $this->assertNotEmpty($result);
        $this->assertNull($cachedResult);
    }

    public function test_uncached_related_model_doesnt_cache()
    {
        $bookId = (new Store)
            ->disableModelCaching()
            ->with('books')
            ->first()
            ->books
            ->first()
            ->id;
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:book-store:genealabslaravelmodelcachingcachedbelongstomany-book_store.book_id_=_{$bookId}");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesuncachedstore",
        ];

        $result = (new BookWithUncachedStore)
            ->find($bookId)
            ->uncachedStores;
        $cachedResult = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value']
            ?? null;
        $uncachedResult = (new UncachedBook)
            ->find($bookId)
            ->stores;

        $this->assertEquals($uncachedResult->pluck('id'), $result->pluck('id'));
        $this->assertNull($cachedResult);
        $this->assertNotNull($result);
        $this->assertNotNull($uncachedResult);
    }

    public function test_invalidating_cache_when_syncing()
    {
        $bookId = (new Store)
            ->disableModelCaching()
            ->with('books')
            ->first()
            ->books
            ->first()
            ->id;
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:stores:genealabslaravelmodelcachingtestsfixturesstore-testing:{$this->testingSqlitePath}testing.sqlite:books-first");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesstore",
        ];
        $newStores = Store::factory()->count(2)->create();
        $result = Book::find($bookId)
            ->stores;

        Book::find($bookId)
            ->stores()
            ->attach($newStores[0]->id);
        Book::find($bookId)
            ->stores()
            ->sync($newStores->pluck('id'));
        $cachedResult = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value']
            ?? null;

        $this->assertEmpty(array_diff(
            Book::find($bookId)->stores()->pluck((new Store)->getTable().'.id')->toArray(),
            $newStores->pluck('id')->toArray()
        ));
        $this->assertNotEmpty($result);
        $this->assertNull($cachedResult);
    }
}
