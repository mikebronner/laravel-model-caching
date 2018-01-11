<?php namespace GeneaLabs\LaravelModelCaching\Tests\Unit\Console\Commands;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Profile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\UnitTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FlushTest extends UnitTestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        cache()->flush();
        $publishers = factory(Publisher::class, 10)->create();
        factory(Author::class, 10)->create()
            ->each(function ($author) use ($publishers) {
                factory(Book::class, random_int(2, 10))->make()
                    ->each(function ($book) use ($author, $publishers) {
                        $book->author()->associate($author);
                        $book->publisher()->associate($publishers[rand(0, 9)]);
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

    public function testGivenModelIsFlushed()
    {
        $authors = (new Author)->all();
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = cache()
            ->tags($tags)
            ->get($key);
        $result = $this->artisan('modelCache:flush', ['--model' => Author::class]);
        $flushedResults = cache()
            ->tags($tags)
            ->get($key);

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($flushedResults);
        $this->assertEquals($result, 0);
    }

    public function testGivenModelWithRelationshipIsFlushed()
    {
        $authors = (new Author)->with('books')->get();
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $cachedResults = cache()
            ->tags($tags)
            ->get($key);
        $result = $this->artisan(
            'modelCache:flush',
            ['--model' => Author::class]
        );
        $flushedResults = cache()
            ->tags($tags)
            ->get($key);

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($flushedResults);
        $this->assertEquals($result, 0);
    }

    public function testNonCachedModelsCannotBeFlushed()
    {
        $result = $this->artisan(
            'modelCache:flush',
            ['--model' => UncachedAuthor::class]
        );

        $this->assertEquals($result, 1);
    }

    public function testModelOptionIsSpecified()
    {
        $result = $this->artisan('modelCache:flush', []);

        $this->assertEquals($result, 1);
    }
}
