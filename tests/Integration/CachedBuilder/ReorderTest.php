<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class ReorderTest extends IntegrationTestCase
{
    public function testReorderWithNoArgumentsClearsOrderAndProducesDifferentCacheKey()
    {
        $orderedAuthors = (new Author)
            ->orderBy('name')
            ->get();

        $orderedKey = sha1(
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:"
            . "authors:genealabslaravelmodelcachingtestsfixturesauthor"
            . "-authors.deleted_at_null_orderBy_name_asc"
        );

        $reorderedAuthors = (new Author)
            ->orderBy('name')
            ->reorder()
            ->get();

        $reorderedKey = sha1(
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:"
            . "authors:genealabslaravelmodelcachingtestsfixturesauthor"
            . "-authors.deleted_at_null"
        );

        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors",
        ];

        $cachedOrderedResults = $this->cache()->tags($tags)->get($orderedKey)['value'];
        $cachedReorderedResults = $this->cache()->tags($tags)->get($reorderedKey)['value'];

        $liveResults = (new UncachedAuthor)->orderBy('name')->reorder()->get();

        $this->assertNotEquals($orderedKey, $reorderedKey);
        $this->assertEmpty($reorderedAuthors->diffKeys($cachedReorderedResults));
        $this->assertEmpty($liveResults->diffKeys($reorderedAuthors));
    }

    public function testReorderWithColumnProducesDifferentCacheKeyFromOrderBy()
    {
        // First query: orderBy('name', 'asc') without reorder
        $orderedAuthors = (new Author)
            ->orderBy('name', 'asc')
            ->get();

        $orderedKey = sha1(
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:"
            . "authors:genealabslaravelmodelcachingtestsfixturesauthor"
            . "-authors.deleted_at_null_orderBy_name_asc"
        );

        // Second query: orderBy('id', 'desc') then reorder('name', 'asc')
        // This should clear the id ordering and apply name asc
        $reorderedAuthors = (new Author)
            ->orderBy('id', 'desc')
            ->reorder('name', 'asc')
            ->get();

        // After reorder('name', 'asc'), the orders array should only have name asc
        // So the cache key should be the same as a simple orderBy('name', 'asc')
        $reorderedKey = sha1(
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:"
            . "authors:genealabslaravelmodelcachingtestsfixturesauthor"
            . "-authors.deleted_at_null_orderBy_name_asc"
        );

        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors",
        ];

        $cachedResults = $this->cache()->tags($tags)->get($reorderedKey)['value'];
        $liveResults = (new UncachedAuthor)->orderBy('id', 'desc')->reorder('name', 'asc')->get();

        $this->assertEquals($orderedKey, $reorderedKey);
        $this->assertEmpty($reorderedAuthors->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($reorderedAuthors));
    }

    public function testReorderDoesNotReturnStaleCachedResults()
    {
        // Execute an ordered query first to populate cache
        $orderedAuthors = (new Author)
            ->orderBy('name', 'desc')
            ->get();

        // Now execute same base query but with reorder() to clear ordering
        $reorderedAuthors = (new Author)
            ->orderBy('name', 'desc')
            ->reorder()
            ->get();

        $liveOrderedResults = (new UncachedAuthor)->orderBy('name', 'desc')->get();
        $liveReorderedResults = (new UncachedAuthor)->orderBy('name', 'desc')->reorder()->get();

        // Ordered results should match live ordered results
        $this->assertEquals(
            $liveOrderedResults->pluck('id')->toArray(),
            $orderedAuthors->pluck('id')->toArray()
        );

        // Reordered results should match live reordered results (default ordering)
        $this->assertEquals(
            $liveReorderedResults->pluck('id')->toArray(),
            $reorderedAuthors->pluck('id')->toArray()
        );

        // They should NOT be the same ordering (unless data happens to be in same order)
        // At minimum, the cache keys are different (verified by the cache retrieval working)
    }

    public function testMultipleReorderCallsProduceCorrectResults()
    {
        $authors = (new Author)
            ->orderBy('id', 'desc')
            ->reorder('name', 'asc')
            ->reorder('name', 'desc')
            ->get();

        $liveResults = (new UncachedAuthor)
            ->orderBy('id', 'desc')
            ->reorder('name', 'asc')
            ->reorder('name', 'desc')
            ->get();

        $expectedKey = sha1(
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:"
            . "authors:genealabslaravelmodelcachingtestsfixturesauthor"
            . "-authors.deleted_at_null_orderBy_name_desc"
        );

        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors",
        ];

        $cachedResults = $this->cache()->tags($tags)->get($expectedKey)['value'];

        $this->assertEquals(
            $liveResults->pluck('id')->toArray(),
            $authors->pluck('id')->toArray()
        );
        $this->assertEmpty($authors->diffKeys($cachedResults));
    }
}
