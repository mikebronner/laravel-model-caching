<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class BelongsToManySyncMemoryTest extends IntegrationTestCase
{
    public function testSyncCompletesWithoutMemoryExhaustion(): void
    {
        $book = (new Book)
            ->disableModelCaching()
            ->first();
        $stores = Store::factory()->count(50)->create();
        $storeIds = $stores->pluck('id')->toArray();

        $memoryBefore = memory_get_usage(true);
        $result = $book->stores()->sync($storeIds);
        $memoryAfter = memory_get_usage(true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('attached', $result);
        $this->assertArrayHasKey('detached', $result);
        $this->assertArrayHasKey('updated', $result);

        // Memory should not grow by more than 10MB for a 50-record sync
        $memoryGrowth = $memoryAfter - $memoryBefore;
        $this->assertLessThan(
            10 * 1024 * 1024,
            $memoryGrowth,
            "Memory grew by " . round($memoryGrowth / 1024 / 1024, 2) . "MB during sync — possible memory leak."
        );
    }

    public function testSyncInvalidatesCacheCorrectly(): void
    {
        $book = (new Book)
            ->disableModelCaching()
            ->first();
        $newStores = Store::factory()->count(3)->create();

        // Load stores into cache
        $cachedStores = Book::find($book->id)->stores;
        $this->assertNotNull($cachedStores);

        // Sync with new stores
        $book->stores()->sync($newStores->pluck('id'));

        // After sync, cached result should be invalidated
        // Fresh query should return the new stores
        $freshStores = Book::find($book->id)->stores;
        $freshStoreIds = $freshStores->pluck('id')->sort()->values()->toArray();
        $expectedIds = $newStores->pluck('id')->sort()->values()->toArray();

        $this->assertEquals($expectedIds, $freshStoreIds);
    }
}
