<?php namespace GeneaLabs\LaravelModelCaching\Tests\Unit;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CacheTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        factory(Author::class, 10)->create()
            ->each(function($author) {
                factory(Book::class, random_int(2, 10))->make()
                    ->each(function ($book) use ($author) {
                        $book->author()->associate($author);
                        $book->save();
                    });
            });
    }

    public function testCacheIsEmptyBeforeLoadingModels()
    {
        $this->assertNull(cache()->get('genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks'));
    }

    public function testCacheIsNotEmptyAfterLoadingModels()
    {
        (new Author)->with('books')->get();

        $this->assertNotNull(cache()->get('genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks'));
    }
}
