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

    // public function testCacheIsEmptyBeforeLoadingModels()
    // {
    //     $results = cache()->tags([
    //             'genealabslaravelmodelcachingtestsfixturesauthor',
    //             'genealabslaravelmodelcachingtestsfixturesbook'
    //         ])
    //         ->get('genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks');
    //
    //     $this->assertNull($results);
    // }
    //
    // public function testCacheIsNotEmptyAfterLoadingModels()
    // {
    //     (new Author)->with('books')->get();
    //
    //     $results = cache()->tags([
    //             'genealabslaravelmodelcachingtestsfixturesauthor',
    //             'genealabslaravelmodelcachingtestsfixturesbook'
    //         ])
    //         ->get('genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks');
    //
    //     $this->assertNotNull($results);
    // }
    //
    // public function testCreatingModelClearsCache()
    // {
    //     (new Author)->with('books')->get();
    //
    //     factory(Author::class)->create();
    //
    //     $results = cache()->tags([
    //             'genealabslaravelmodelcachingtestsfixturesauthor',
    //             'genealabslaravelmodelcachingtestsfixturesbook'
    //         ])
    //         ->get('genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks');
    //
    //     $this->assertNull($results);
    // }
    //
    // public function testUpdatingModelClearsCache()
    // {
    //     $author = (new Author)->with('books')->get()->first();
    //     $author->name = "John Jinglheimer";
    //     $author->save();
    //
    //     $results = cache()->tags([
    //             'genealabslaravelmodelcachingtestsfixturesauthor',
    //             'genealabslaravelmodelcachingtestsfixturesbook'
    //         ])
    //         ->get('genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks');
    //
    //     $this->assertNull($results);
    // }
    //
    // public function testDeletingModelClearsCache()
    // {
    //     $author = (new Author)->with('books')->get()->first();
    //     $author->delete();
    //
    //     $results = cache()->tags([
    //             'genealabslaravelmodelcachingtestsfixturesauthor',
    //             'genealabslaravelmodelcachingtestsfixturesbook'
    //         ])
    //         ->get('genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks');
    //
    //     $this->assertNull($results);
    // }
    //
    // public function testHasManyRelationshipIsCached()
    // {
    //     $authors = (new Author)->with('books')->get();
    //     $authorIds = implode('_', $authors->pluck('id')->toArray());
    //
    //     $results = collect(cache()->tags([
    //             'genealabslaravelmodelcachingtestsfixturesauthor',
    //             'genealabslaravelmodelcachingtestsfixturesbook'
    //         ])
    //         ->get("genealabslaravelmodelcachingtestsfixturesauthor_{$authorIds}-genealabslaravelmodelcachingtestsfixturesbooks"));
    //
    //     $this->assertNotNull($results);
    //     $this->assertEmpty($authors->diffAssoc($results));
    //     $this->assertNotEmpty($authors);
    //     $this->assertNotEmpty($results);
    //     $this->assertEquals($authors->count(), $results->count());
    // }
    //
    // public function testBelongsToRelationshipIsCached()
    // {
    //     $books = (new Book)->with('author')->get();
    //     $bookIds = implode('_', $books->pluck('id')->toArray());
    //
    //     $results = collect(cache()->tags([
    //             'genealabslaravelmodelcachingtestsfixturesbook',
    //             'genealabslaravelmodelcachingtestsfixturesauthor'
    //         ])
    //         ->get("genealabslaravelmodelcachingtestsfixturesbook_{$bookIds}-genealabslaravelmodelcachingtestsfixturesauthors"));
    //
    //     $this->assertNotNull($results);
    //     $this->assertEmpty($books->diffAssoc($results));
    //     $this->assertNotEmpty($books);
    //     $this->assertNotEmpty($results);
    //     $this->assertEquals($books->count(), $results->count());
    // }
    //
    // public function testBelongsToManyRelationshipIsCached()
    // {
    //     $books = (new Book)->with('stores')->get();
    //     $bookIds = implode('_', $books->pluck('id')->toArray());
    //
    //     $results = collect(cache()->tags([
    //             'genealabslaravelmodelcachingtestsfixturesbook',
    //             'genealabslaravelmodelcachingtestsfixturesstore'
    //         ])
    //         ->get("genealabslaravelmodelcachingtestsfixturesbook_{$bookIds}-genealabslaravelmodelcachingtestsfixturesstores"));
    //
    //     $this->assertNotNull($results);
    //     $this->assertEmpty($books->diffAssoc($results));
    //     $this->assertNotEmpty($books);
    //     $this->assertNotEmpty($results);
    //     $this->assertEquals($books->count(), $results->count());
    // }
    //
    // public function testHasOneRelationshipIsCached()
    // {
    //     $authors = (new Author)->with('profile')->get();
    //     $authorIds = implode('_', $authors->pluck('id')->toArray());
    //
    //     $results = collect(cache()
    //         ->tags([
    //             'genealabslaravelmodelcachingtestsfixturesauthor',
    //             'genealabslaravelmodelcachingtestsfixturesprofile'
    //         ])
    //         ->get("genealabslaravelmodelcachingtestsfixturesauthor_{$authorIds}-genealabslaravelmodelcachingtestsfixturesprofiles"));
    //
    //     $this->assertNotNull($results);
    //     $this->assertEmpty($authors->diffAssoc($results));
    //     $this->assertNotEmpty($authors);
    //     $this->assertNotEmpty($results);
    //     $this->assertEquals($authors->count(), $results->count());
    // }

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

    public function testChunkModelResultsCreatesCache()
    {
        $cachedChunks = collect([
            'authors' => collect(),
            'keys' => collect(),
        ]);
        $chunkSize = 3;
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];
        $uncachedChunks = collect();

        (new Author)->with('books', 'profile')
            ->chunk($chunkSize, function ($chunk) use (&$cachedChunks, $chunkSize) {
                $offset = '';

                if ($cachedChunks['authors']->count()) {
                    $offsetIncrement = $cachedChunks['authors']->count() * $chunkSize;
                    $offset = "-offset_{$offsetIncrement}";
                }

                $cachedChunks['authors']->push($chunk);
                $cachedChunks['keys']->push("genealabslaravelmodelcachingtestsfixturesauthor-books-profile{$offset}-limit_3");
            });

        (new UncachedAuthor)->with('books', 'profile')
            ->chunk($chunkSize, function ($chunk) use (&$uncachedChunks) {
                $uncachedChunks->push($chunk);
            });

        for ($index = 0; $index < $cachedChunks['authors']->count(); $index++) {
            $key = $cachedChunks['keys'][$index];
            $cachedResults = cache()->tags($tags)
                ->get($key);

            $this->assertEmpty($cachedChunks['authors'][$index]->diffAssoc($cachedResults));
            $this->assertEmpty($uncachedChunks[$index]->diffAssoc($cachedResults));
        }
    }

    public function testCountModelResultsCreatesCache()
    {
        $authors = (new Author)->with('books', 'profile')
            ->count();
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-count';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResults = cache()->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)->with('books', 'profile')
            ->count();

        $this->assertEquals($authors, $cachedResults);
        $this->assertEquals($liveResults, $cachedResults);
    }

    public function testFindModelResultsCreatesCache()
    {
        $author = (new Author)->find(1);
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor_1';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResults = collect()->push(cache()->tags($tags)
            ->get($key));
        $liveResults = collect()->push((new UncachedAuthor)->find(1));

        $this->assertEquals($author, $cachedResults->first());
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    public function testGetModelResultsCreatesCache()
    {
        $authors = (new Author)->with('books', 'profile')
            ->get();
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-books-profile';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = cache()->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)->with('books', 'profile')
            ->get();

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    // test cursor()

    // test max()

    // test min()

    // test avg()

    // test value()

    // test pluck()
}
