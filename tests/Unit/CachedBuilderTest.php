<?php namespace GeneaLabs\LaravelModelCaching\Tests\Unit;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Profile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedProfile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPublisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedStore;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Http\Resources\Author as AuthorResource;
use GeneaLabs\LaravelModelCaching\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CachedBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        cache()->flush();
        $publishers = factory(Publisher::class, 10)->create();
        factory(Author::class, 10)->create()
            ->each(function($author) use ($publishers) {
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

    public function testCacheIsEmptyBeforeLoadingModels()
    {
        $results = cache()->tags([
                'genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get('genealabslaravelmodelcachingtestsfixturesauthor-books');

        $this->assertNull($results);
    }

    public function testCacheIsNotEmptyAfterLoadingModels()
    {
        (new Author)->with('books')->get();

        $results = cache()->tags([
                'genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get('genealabslaravelmodelcachingtestsfixturesauthor-books');

        $this->assertNotNull($results);
    }

    public function testCreatingModelClearsCache()
    {
        (new Author)->with('books')->get();

        factory(Author::class)->create();

        $results = cache()->tags([
                'genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get('genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks');

        $this->assertNull($results);
    }

    public function testUpdatingModelClearsCache()
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

    public function testHasManyRelationshipIsCached()
    {
        $authors = (new Author)->with('books')->get();

        $results = collect(cache()->tags([
                'genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get("genealabslaravelmodelcachingtestsfixturesauthor-books"));

        $this->assertNotNull($results);
        $this->assertEmpty($authors->diffAssoc($results));
        $this->assertNotEmpty($authors);
        $this->assertNotEmpty($results);
        $this->assertEquals($authors->count(), $results->count());
    }

    public function testBelongsToRelationshipIsCached()
    {
        $books = (new Book)->with('author')->get();

        $results = collect(cache()->tags([
                'genealabslaravelmodelcachingtestsfixturesbook',
                'genealabslaravelmodelcachingtestsfixturesauthor'
            ])
            ->get("genealabslaravelmodelcachingtestsfixturesbook-author"));

        $this->assertNotNull($results);
        $this->assertEmpty($books->diffAssoc($results));
        $this->assertNotEmpty($books);
        $this->assertNotEmpty($results);
        $this->assertEquals($books->count(), $results->count());
    }

    public function testBelongsToManyRelationshipIsCached()
    {
        $books = (new Book)->with('stores')->get();

        $results = collect(cache()->tags([
                'genealabslaravelmodelcachingtestsfixturesbook',
                'genealabslaravelmodelcachingtestsfixturesstore'
            ])
            ->get("genealabslaravelmodelcachingtestsfixturesbook-stores"));

        $this->assertNotNull($results);
        $this->assertEmpty($books->diffAssoc($results));
        $this->assertNotEmpty($books);
        $this->assertNotEmpty($results);
        $this->assertEquals($books->count(), $results->count());
    }

    public function testHasOneRelationshipIsCached()
    {
        $authors = (new Author)->with('profile')->get();

        $results = collect(cache()
            ->tags([
                'genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabslaravelmodelcachingtestsfixturesprofile'
            ])
            ->get("genealabslaravelmodelcachingtestsfixturesauthor-profile"));

        $this->assertNotNull($results);
        $this->assertEmpty($authors->diffAssoc($results));
        $this->assertNotEmpty($authors);
        $this->assertNotEmpty($results);
        $this->assertEquals($authors->count(), $results->count());
    }

    public function testAvgModelResultsCreatesCache()
    {
        $authorId = (new Author)->with('books', 'profile')
            ->avg('id');
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-avg_id';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResult = cache()->tags($tags)
            ->get($key);
        $liveResult = (new UncachedAuthor)->with('books', 'profile')
            ->avg('id');

        $this->assertEquals($authorId, $cachedResult);
        $this->assertEquals($liveResult, $cachedResult);
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
                $cachedChunks['keys']->push("genealabslaravelmodelcachingtestsfixturesauthor-books-profile_orderBy_authors.id_asc{$offset}-limit_3");
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

    public function testCursorModelResultsCreatesCache()
    {
        $authors = (new Author)->with('books', 'profile')
            ->cursor();
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-cursor';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResults = cache()->tags($tags)
            ->get($key);
        $liveResults = collect((new UncachedAuthor)->with('books', 'profile')
            ->cursor());

        $this->assertEmpty($authors->diffAssoc($cachedResults));
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    public function testFindModelResultsCreatesCache()
    {
        $author = collect()->push((new Author)->find(1));
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor_1';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResults = collect()->push(cache()->tags($tags)
            ->get($key));
        $liveResults = collect()->push((new UncachedAuthor)->find(1));

        $this->assertEmpty($author->diffAssoc($cachedResults));
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

    public function testMaxModelResultsCreatesCache()
    {
        $authorId = (new Author)->with('books', 'profile')
            ->max('id');
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-max_id';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResult = cache()->tags($tags)
            ->get($key);
        $liveResult = (new UncachedAuthor)->with('books', 'profile')
            ->max('id');

        $this->assertEquals($authorId, $cachedResult);
        $this->assertEquals($liveResult, $cachedResult);
    }

    public function testMinModelResultsCreatesCache()
    {
        $authorId = (new Author)->with('books', 'profile')
            ->min('id');
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-min_id';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResult = cache()->tags($tags)
            ->get($key);
        $liveResult = (new UncachedAuthor)->with('books', 'profile')
            ->min('id');

        $this->assertEquals($authorId, $cachedResult);
        $this->assertEquals($liveResult, $cachedResult);
    }

    public function testPluckModelResultsCreatesCache()
    {
        $authors = (new Author)->with('books', 'profile')
            ->pluck('name', 'id');
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor_name-books-profile-pluck_name_id';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = cache()->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)->with('books', 'profile')
            ->pluck('name', 'id');

        $this->assertEmpty($authors->diffAssoc($cachedResults));
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    public function testSumModelResultsCreatesCache()
    {
        $authorId = (new Author)->with('books', 'profile')
            ->sum('id');
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-sum_id';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResult = cache()->tags($tags)
            ->get($key);
        $liveResult = (new UncachedAuthor)->with('books', 'profile')
            ->sum('id');

        $this->assertEquals($authorId, $cachedResult);
        $this->assertEquals($liveResult, $cachedResult);
    }

    public function testValueModelResultsCreatesCache()
    {
        $authors = (new Author)->with('books', 'profile')
            ->value('name');
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor_name-books-profile-first';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = cache()->tags($tags)
            ->get($key)
            ->name;

        $liveResults = (new UncachedAuthor)->with('books', 'profile')
            ->value('name');

        $this->assertEquals($authors, $cachedResults);
        $this->assertEquals($liveResults, $cachedResults);
    }

    public function testNestedRelationshipEagerloading()
    {
        $authors = collect([(new Author)->with('books.publisher')
                ->first()]);

        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-books-books.publisher-first';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturespublisher',
        ];

        $cachedResults = collect([cache()->tags($tags)
                ->get($key)]);
        $liveResults = collect([(new UncachedAuthor)->with('books.publisher')
                ->first()]);

        $this->assertEmpty($authors->diffAssoc($cachedResults));
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    public function testLazyLoadedRelationshipResolvesThroughCachedBuilder()
    {
        $books = (new Author)->first()->books;
        $key = 'genealabslaravelmodelcachingtestsfixturesbook-books.author_id_1-books.author_id_notnull';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $cachedResults = cache()->tags($tags)->get($key);
        $liveResults = (new UncachedAuthor)->first()->books;

        $this->assertEmpty($books->diffAssoc($cachedResults));
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    public function testLazyLoadingOnResourceIsCached()
    {
        $books = (new AuthorResource((new Author)->first()))->books;
        $key = 'genealabslaravelmodelcachingtestsfixturesbook-books.author_id_1-books.author_id_notnull';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $cachedResults = cache()->tags($tags)->get($key);
        $liveResults = (new UncachedAuthor)->first()->books;

        $this->assertEmpty($books->diffAssoc($cachedResults));
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    public function testOrderByClauseParsing()
    {
        $authors = (new Author)->orderBy('name')->get();

        $key = 'genealabslaravelmodelcachingtestsfixturesauthor_orderBy_name_asc';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResults = cache()->tags($tags)->get($key);
        $liveResults = (new UncachedAuthor)->orderBy('name')->get();

        $this->assertEmpty($authors->diffAssoc($cachedResults));
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    public function testNestedRelationshipWhereClauseParsing()
    {
        $authors = (new Author)->with('books.publisher')
            ->get();

        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-books-books.publisher';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturespublisher',
        ];

        $cachedResults = cache()->tags($tags)
            ->get($key);

        $liveResults = (new UncachedAuthor)->with('books.publisher')
            ->get();

        $this->assertEmpty($authors->diffAssoc($cachedResults));
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    public function testExistsRelationshipWhereClauseParsing()
    {

        $authors = collect([(new Author)->whereHas('books')->first()]);

        $key = 'genealabslaravelmodelcachingtestsfixturesauthor_and_authors.id_=_books.author_id-first';
        $tags = ['genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = collect([cache()->tags($tags)->get($key)]);

        $liveResults = collect([(new UncachedAuthor)
            ->whereHas('books')->first()]);

        $this->assertTrue($authors->diffAssoc($cachedResults)->isEmpty());
        $this->assertTrue($liveResults->diffAssoc($cachedResults)->isEmpty());        

    }

    public function testColumnsRelationshipWhereClauseParsing()
    {
        // ???
        $this->markTestIncomplete();
    }

    public function testRawWhereClauseParsing()
    {
        $authors = collect([(new Author)
            ->whereRaw('name <> \'\'')->first()]);

        $key = 'genealabslaravelmodelcachingtestsfixturesauthor_and_name-first';
        $tags = ['genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = collect([cache()->tags($tags)->get($key)]);

        $liveResults = collect([(new UncachedAuthor)
            ->whereRaw('name <> \'\'')->first()]);

        $this->assertTrue($authors->diffAssoc($cachedResults)->isEmpty());
        $this->assertTrue($liveResults->diffAssoc($cachedResults)->isEmpty());
    }
}
