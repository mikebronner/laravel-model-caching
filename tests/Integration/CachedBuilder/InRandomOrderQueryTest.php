<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class InRandomOrderQueryTest extends IntegrationTestCase
{
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

        $this->assertNotEquals($book1, $book2);
        $this->assertNotEquals($cachedBook1, $cachedBook2);
    }
}
