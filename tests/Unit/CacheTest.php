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

        cache()->flush();

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
        $results = cache()->tags([
                'genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get('genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks');

        $this->assertNull($results);
    }

    public function testCacheIsNotEmptyAfterLoadingModels()
    {
        (new Author)->with('books')->get()->first();

        $results = cache()->tags([
                'genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get('genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks');

        $this->assertNotNull($results);
    }

    public function testCreatingModelClearsCache()
    {
        $author = (new Author)->with('books')->get()->first();
        $author->name = "John Jinglheimer";
        $author->save();

        $results = cache()->tags([
                'genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get('genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks');

        $this->assertNull($results);
    }

    public function testDeletingModelClearsCache()
    {
        $author = (new Author)->with('books')->get()->first();
        $author->delete();

        $results = cache()->tags([
                'genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get('genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks');

        $this->assertNull($results);
    }
}
