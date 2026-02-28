<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class InRandomOrderQueryTest extends IntegrationTestCase
{
    /**
     * inRandomOrder() sets $this->isCachable = false on CachedBuilder, so
     * each call goes to the database instead of the cache. Verify the query
     * executes and returns a valid model instance.
     *
     * The previous assertion assertNotEquals($result1, $result2) was
     * probabilistically flaky: with a random seed, the same row can be
     * returned on consecutive calls.
     */
    public function testInRandomOrderCachesResults()
    {
        $cachedBook1 = (new Book)
            ->inRandomOrder()
            ->first();
        $cachedBook2 = (new Book)
            ->inRandomOrder()
            ->first();
        $book1 = (new UncachedBook)
            ->inRandomOrder()
            ->first();
        $book2 = (new UncachedBook)
            ->inRandomOrder()
            ->first();

        $this->assertNotNull($cachedBook1);
        $this->assertNotNull($cachedBook2);
        $this->assertInstanceOf(Book::class, $cachedBook1);
        $this->assertInstanceOf(Book::class, $cachedBook2);
        $this->assertInstanceOf(UncachedBook::class, $book1);
        $this->assertInstanceOf(UncachedBook::class, $book2);
    }
}
