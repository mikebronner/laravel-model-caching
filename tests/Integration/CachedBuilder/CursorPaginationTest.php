<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class CursorPaginationTest extends IntegrationTestCase
{
    public function testCursorPaginateOnCachedModelReturnsCorrectResults()
    {
        $cachedResults = (new Author)->orderBy('id')->cursorPaginate(5);
        $uncachedResults = (new UncachedAuthor)->orderBy('id')->cursorPaginate(5);

        $this->assertEquals($uncachedResults->count(), $cachedResults->count());
        $this->assertEquals(
            $uncachedResults->pluck('id')->toArray(),
            $cachedResults->pluck('id')->toArray()
        );
    }

    public function testCursorPaginateWithCursorReturnsNextPage()
    {
        $firstPage = (new Author)->orderBy('id')->cursorPaginate(2);
        $cursor = $firstPage->nextCursor();

        if ($cursor) {
            $secondPage = (new Author)->orderBy('id')->cursorPaginate(2, ['*'], 'cursor', $cursor);
            $uncachedSecondPage = (new UncachedAuthor)->orderBy('id')->cursorPaginate(2, ['*'], 'cursor', $cursor);

            $this->assertEquals(
                $uncachedSecondPage->pluck('id')->toArray(),
                $secondPage->pluck('id')->toArray()
            );
        } else {
            $this->markTestSkipped('Not enough authors for cursor pagination test');
        }
    }

    public function testRowValuesWhereClauseSerializesIntoCacheKey()
    {
        // Directly test that whereRowValues doesn't crash cache key generation
        // by fetching results — the get() call triggers makeCacheKey internally
        $results = (new Author)
            ->whereRowValues(['id', 'name'], '>', [1, 'test'])
            ->orderBy('id')
            ->get();

        $this->assertNotNull($results);
    }

    public function testRowValuesWhereProducesDifferentCacheKeys()
    {
        $query1 = (new Author)
            ->whereRowValues(['id', 'name'], '>', [1, 'a'])
            ->orderBy('id');

        $query2 = (new Author)
            ->whereRowValues(['id', 'name'], '>', [2, 'b'])
            ->orderBy('id');

        $cacheKey1 = (new \ReflectionMethod($query1, 'makeCacheKey'))
            ->invoke($query1);
        $cacheKey2 = (new \ReflectionMethod($query2, 'makeCacheKey'))
            ->invoke($query2);

        $this->assertNotEquals($cacheKey1, $cacheKey2);
    }
}
