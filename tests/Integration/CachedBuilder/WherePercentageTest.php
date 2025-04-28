<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class WherePercentageTest extends IntegrationTestCase
{
    public function testWithQuery(): void
    {
        $books = (new Book)
            ->whereIn('description', ['10%', '20%'])
            ->get();

        $uncachedBooks = (new UncachedBook)
            ->whereIn('description', ['10%', '20%'])
            ->get();

        $this->assertEquals($books->pluck("id"), $uncachedBooks->pluck("id"));
    }
}
