<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Printer;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Support\Facades\DB;

class HasManyThroughCacheInvalidationTest extends IntegrationTestCase
{
    public function testHasManyThroughCacheInvalidatedWhenIntermediateModelCreated(): void
    {
        $author = (new Author)->first();
        $initialCount = $author->printers()->count();

        $book = Book::factory()->create(['author_id' => $author->id]);
        Printer::factory()->create(['book_id' => $book->id]);

        $cachedCount = $author->printers()->count();
        $rawCount = DB::table('printers')
            ->join('books', 'books.id', '=', 'printers.book_id')
            ->where('books.author_id', $author->id)
            ->count();

        $this->assertEquals($rawCount, $cachedCount);
        $this->assertEquals($initialCount + 1, $cachedCount);
    }

    public function testHasManyThroughCacheInvalidatedWhenIntermediateModelDeleted(): void
    {
        $author = (new Author)->first();
        $initialCount = $author->printers()->count();
        $this->assertGreaterThan(0, $initialCount);

        $book = Book::where('author_id', $author->id)->first();
        $book->delete();

        $rawCount = DB::table('printers')
            ->join('books', 'books.id', '=', 'printers.book_id')
            ->where('books.author_id', $author->id)
            ->count();

        $cachedCount = $author->printers()->count();

        $this->assertEquals($rawCount, $cachedCount, 'HasManyThrough cache should be invalidated when intermediate model is deleted.');
    }
}
