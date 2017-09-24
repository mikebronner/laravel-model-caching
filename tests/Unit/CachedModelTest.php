<?php namespace GeneaLabs\LaravelModelCaching\Tests\Unit;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Profile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedProfile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedStore;
use GeneaLabs\LaravelModelCaching\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CachedModelTest extends TestCase
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
                factory(Profile::class)->make([
                    'author_id' => $author->id,
                ]);
            });

        $bookIds = (new Book)->all()->pluck('id');
        factory(Store::class, 10)->create()
            ->each(function ($store) use ($bookIds) {
                $store->books()->sync(rand($bookIds->min(), $bookIds->max()));
            });
        cache()->flush();
    }

    public function testAllModelResultsCreatesCache()
    {
        $authors = (new Author)->all();
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResults = cache()->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)->all();

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }
}
