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
use GeneaLabs\LaravelModelCaching\Tests\UnitTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
* @SuppressWarnings(PHPMD.TooManyPublicMethods)
* @SuppressWarnings(PHPMD.TooManyMethods)
 */
class CachedBuilderTest extends UnitTestCase
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
            ->get(sha1('genealabslaravelmodelcachingtestsfixturesauthor-books'));

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
            ->get(sha1(
                'genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_' .
                '7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks'
            ));

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
            ->get(sha1(
                'genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_' .
                '7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks'
            ));

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
            ->get(sha1(
                'genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_' .
                '7_8_9_10-genealabslaravelmodelcachingtestsfixturesbooks'
            ));

        $this->assertNull($results);
    }

    public function testHasManyRelationshipIsCached()
    {
        $authors = (new Author)->with('books')->get();

        $results = collect(cache()->tags([
                'genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get(sha1("genealabslaravelmodelcachingtestsfixturesauthor-books")));

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
            ->get(sha1("genealabslaravelmodelcachingtestsfixturesbook-author")));

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
            ->get(sha1("genealabslaravelmodelcachingtestsfixturesbook-stores")));

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
            ->get(sha1("genealabslaravelmodelcachingtestsfixturesauthor-profile")));

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
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile-avg_id');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
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
                $cachedChunks['keys']->push(sha1(
                    "genealabslaravelmodelcachingtestsfixturesauthor-books-pr" .
                    "ofile_orderBy_authors.id_asc{$offset}-limit_3"
                ));
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
        $authors = (new Author)
            ->with('books', 'profile')
            ->count();
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile-count');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
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
        $authors = (new Author)
            ->with('books', 'profile')
            ->cursor();
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile-cursor');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = cache()
            ->tags($tags)
            ->get($key);
        $liveResults = collect(
            (new UncachedAuthor)
                ->with('books', 'profile')
                ->cursor()
        );

        $this->assertEmpty($authors->diffAssoc($cachedResults));
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    public function testFindModelResultsCreatesCache()
    {
        $author = collect()->push((new Author)->find(1));
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor_1');
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
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile');
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
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile-max_id');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
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
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile-min_id');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
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
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor_name-books-profile-pluck_name_id');
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
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile-sum_id');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
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
        $authorName = (new Author)->with('books', 'profile')
            ->value('name');
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile-value_name');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = cache()->tags($tags)
            ->get($key);
        $liveResult = (new UncachedAuthor)->with('books', 'profile')
            ->value('name');

        $this->assertEquals($authorName, $cachedResult);
        $this->assertEquals($authorName, $liveResult);
    }

    public function testNestedRelationshipEagerLoading()
    {
        $authors = collect([(new Author)->with('books.publisher')
                ->first()]);

        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-books.publisher-first');
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
        $key = sha1('genealabslaravelmodelcachingtestsfixturesbook-books.author_id_1-books.author_id_notnull');
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
        $key = sha1('genealabslaravelmodelcachingtestsfixturesbook-books.author_id_1-books.author_id_notnull');
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

        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor_orderBy_name_asc');
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

        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-books.publisher');
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
        $authors = (new Author)->whereHas('books')
            ->get();

        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor_exists_and_authors.id_=_books.author_id');
        $tags = ['genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = cache()->tags($tags)->get($key);
        $liveResults = (new UncachedAuthor)->whereHas('books')
            ->get();

        $this->assertEmpty($authors->diffAssoc($cachedResults));
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    public function testDoesntHaveWhereClauseParsing()
    {
        $authors = (new Author)
            ->doesntHave('books')
            ->get();

        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor_notexists_and_authors.id_=_books.author_id');
        $tags = ['genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = cache()
            ->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)
            ->doesntHave('books')
            ->get();

        $this->assertEmpty($authors->diffAssoc($cachedResults));
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    public function testColumnsRelationshipWhereClauseParsing()
    {
        $author = (new Author)
            ->orderBy('name')
            ->first();
        $authors = (new Author)
            ->where('name', '=', $author->name)
            ->get();
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-name_' .
            $author->name);
        $tags = ['genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = cache()
            ->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)
            ->where('name', '=', $author->name)
            ->get();

        $this->assertEmpty($authors->diffAssoc($cachedResults));
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    public function testRawWhereClauseParsing()
    {
        $authors = collect([(new Author)
            ->whereRaw('name <> \'\'')
            ->first()]);

        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor_and_name-first');
        $tags = ['genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = collect([cache()->tags($tags)->get($key)]);

        $liveResults = collect([(new UncachedAuthor)
            ->whereRaw('name <> \'\'')->first()]);

        $this->assertTrue($authors->diffAssoc($cachedResults)->isEmpty());
        $this->assertTrue($liveResults->diffAssoc($cachedResults)->isEmpty());
    }

    public function testScopeClauseParsing()
    {
        $author = factory(Author::class, 1)
            ->create(['name' => 'Anton'])
            ->first();
        $authors = (new Author)
            ->startsWithA()
            ->get();
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-name_A%');
        $tags = ['genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = cache()->tags($tags)->get($key);
        $liveResults = (new UncachedAuthor)
            ->startsWithA()
            ->get();

        $this->assertTrue($authors->contains($author));
        $this->assertTrue($cachedResults->contains($author));
        $this->assertTrue($liveResults->contains($author));
    }

    public function testRelationshipQueriesAreCached()
    {
        $books = (new Author)
            ->first()
            ->books()
            ->get();
        $key = sha1('genealabslaravelmodelcachingtestsfixturesbook-books.author_id_1-books.author_id_notnull');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesbook'
        ];

        $cachedResults = cache()->tags($tags)->get($key);
        $liveResults = (new UncachedAuthor)
            ->first()
            ->books()
            ->get();

        $this->assertTrue($cachedResults->diffAssoc($books)->isEmpty());
        $this->assertTrue($liveResults->diffAssoc($books)->isEmpty());
    }

    public function testRawOrderByWithoutColumnReference()
    {
        $authors = (new Author)
            ->orderByRaw('DATE()')
            ->get();

        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor_orderByRaw_date');
        $tags = ['genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = cache()
            ->tags($tags)
            ->get($key);

        $liveResults = (new UncachedAuthor)
            ->orderByRaw('DATE()')
            ->get();

        $this->assertTrue($cachedResults->diffAssoc($authors)->isEmpty());
        $this->assertTrue($liveResults->diffAssoc($authors)->isEmpty());
    }

    public function testDelete()
    {
        $author = (new Author)
            ->first();
        $liveResult = (new UncachedAuthor)
            ->first();
        $authorId = $author->id;
        $liveResultId = $liveResult->id;
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabslaravelmodelcachingtestsfixturesauthor'];

        $author->delete();
        $liveResult->delete();
        $cachedResult = cache()
            ->tags($tags)
            ->get($key);
        $deletedAuthor = (new Author)->find($authorId);

        $this->assertEquals($liveResultId, $authorId);
        $this->assertNull($cachedResult);
        $this->assertNull($deletedAuthor);
    }
}
