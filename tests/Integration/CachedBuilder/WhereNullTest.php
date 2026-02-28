<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class WhereNullTest extends IntegrationTestCase
{
    public function test_where_null_clause()
    {
        $books = (new Book)
            ->whereNull('description')
            ->get();
        $uncachedBooks = (new UncachedBook)
            ->whereNull('description')
            ->get();

        $this->assertEquals($books->pluck('id'), $uncachedBooks->pluck('id'));
    }

    public function test_nested_where_null_clauses()
    {
        $books = (new Book)
            ->where(function ($query) {
                $query->whereNull('description');
            })
            ->get();
        $uncachedBooks = (new UncachedBook)
            ->where(function ($query) {
                $query->whereNull('description');
            })
            ->get();

        $this->assertEquals($books->pluck('id'), $uncachedBooks->pluck('id'));
    }
}
