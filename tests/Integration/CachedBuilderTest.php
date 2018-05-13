<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration;

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
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
* @SuppressWarnings(PHPMD.TooManyPublicMethods)
* @SuppressWarnings(PHPMD.TooManyMethods)
 */
class CachedBuilderTest extends IntegrationTestCase
{
    use RefreshDatabase;

    public function testCacheIsEmptyBeforeLoadingModels()
    {
        $results = $this->cache()->tags([
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-books');

        $this->assertNull($results);
    }

    public function testCacheIsNotEmptyAfterLoadingModels()
    {
        (new Author)->with('books')->get();

        $results = $this->cache()->tags([
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get(sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-books'));

        $this->assertNotNull($results);
    }

    public function testCreatingModelClearsCache()
    {
        (new Author)->with('books')->get();

        factory(Author::class)->create();

        $results = $this->cache()->tags([
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get(sha1(
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_' .
                '7_8_9_10-genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbooks'
            ));

        $this->assertNull($results);
    }

    public function testUpdatingModelClearsCache()
    {
        $author = (new Author)->with('books')->get()->first();
        $author->name = "John Jinglheimer";
        $author->save();

        $results = $this->cache()->tags([
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get(sha1(
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_' .
                '7_8_9_10-genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbooks'
            ));

        $this->assertNull($results);
    }

    public function testDeletingModelClearsCache()
    {
        $author = (new Author)->with('books')->get()->first();
        $author->delete();

        $results = $this->cache()->tags([
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get(sha1(
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor_1_2_3_4_5_6_' .
                '7_8_9_10-genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbooks'
            ));

        $this->assertNull($results);
    }

    public function testHasManyRelationshipIsCached()
    {
        $authors = (new Author)->with('books')->get();

        $results = collect($this->cache()->tags([
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook'
            ])
            ->get(sha1("genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-books"))['value']);

        $this->assertNotNull($results);
        $this->assertEmpty($authors->diffKeys($results));
        $this->assertNotEmpty($authors);
        $this->assertNotEmpty($results);
        $this->assertEquals($authors->count(), $results->count());
    }

    public function testBelongsToRelationshipIsCached()
    {
        $books = (new Book)->with('author')->get();

        $results = collect($this->cache()->tags([
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor'
            ])
            ->get(sha1("genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook-author"))['value']);

        $this->assertNotNull($results);
        $this->assertEmpty($books->diffKeys($results));
        $this->assertNotEmpty($books);
        $this->assertNotEmpty($results);
        $this->assertEquals($books->count(), $results->count());
    }

    public function testBelongsToManyRelationshipIsCached()
    {
        $books = (new Book)->with('stores')->get();

        $results = collect($this->cache()->tags([
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesstore'
            ])
            ->get(sha1("genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook-stores"))['value']);

        $this->assertNotNull($results);
        $this->assertEmpty($books->diffKeys($results));
        $this->assertNotEmpty($books);
        $this->assertNotEmpty($results);
        $this->assertEquals($books->count(), $results->count());
    }

    public function testHasOneRelationshipIsCached()
    {
        $authors = (new Author)->with('profile')->get();

        $results = collect($this->cache()
            ->tags([
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
                'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesprofile'
            ])
            ->get(sha1("genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-profile"))['value']);

        $this->assertNotNull($results);
        $this->assertEmpty($authors->diffKeys($results));
        $this->assertNotEmpty($authors);
        $this->assertNotEmpty($results);
        $this->assertEquals($authors->count(), $results->count());
    }

    public function testAvgModelResultsCreatesCache()
    {
        $authorId = (new Author)->with('books', 'profile')
            ->avg('id');
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-books-profile-avg_id');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResult = (new UncachedAuthor)->with('books', 'profile')
            ->avg('id');

        $this->assertEquals($authorId, $cachedResult);
        $this->assertEquals($liveResult, $cachedResult);
    }

    public function testChunkModelResultsCreatesCache()
    {
        $chunkedAuthors = [];
        $chunkedKeys = [];
        $tags = ["genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor"];
        (new Author)
            ->chunk(3, function ($authors) use (&$chunkedAuthors, &$chunkedKeys) {
                $offset = "";

                if (count($chunkedKeys)) {
                    $offset = "-offset_" . (count($chunkedKeys) * 3);
                }

                $key = "genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor_orderBy_authors.id_asc{$offset}-limit_3";
                array_push($chunkedAuthors, $authors);
                array_push($chunkedKeys, $key);
            });


        for ($index = 0; $index < count($chunkedAuthors); $index++) {
            $cachedAuthors = $this
                ->cache()
                ->tags($tags)
                ->get(sha1($chunkedKeys[$index]))['value'];

            $this->assertEquals(count($chunkedAuthors[$index]), count($cachedAuthors));
            $this->assertEquals($chunkedAuthors[$index], $cachedAuthors);
        }

        $this->assertCount(4, $chunkedAuthors);
    }

    public function testCountModelResultsCreatesCache()
    {
        $authors = (new Author)
            ->with('books', 'profile')
            ->count();
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-books-profile-count');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = $this->cache()->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->with('books', 'profile')
            ->count();

        $this->assertEquals($authors, $cachedResults);
        $this->assertEquals($liveResults, $cachedResults);
    }

    public function testCountWithStringCreatesCache()
    {
        $authors = (new Author)
            ->with('books', 'profile')
            ->count("id");
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor_id-books-profile-count');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = $this->cache()->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->with('books', 'profile')
            ->count("id");

        $this->assertEquals($authors, $cachedResults);
        $this->assertEquals($liveResults, $cachedResults);
    }

    public function testFindModelResultsCreatesCache()
    {
        $author = collect()->push((new Author)->find(1));
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor_1');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResults = collect()->push($this->cache()->tags($tags)
            ->get($key));
        $liveResults = collect()->push((new UncachedAuthor)->find(1));

        $this->assertEmpty($author->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testFirstModelResultsCreatesCache()
    {
        $author = (new Author)
            ->first();
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-first');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResult = $this->cache()->tags($tags)
            ->get($key)['value'];

        $liveResult = (new UncachedAuthor)
            ->with('books', 'profile')
            ->first();

        $this->assertEquals($cachedResult->id, $author->id);
        $this->assertEquals($liveResult->id, $author->id);
    }

    public function testGetModelResultsCreatesCache()
    {
        $authors = (new Author)->with('books', 'profile')
            ->get();
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-books-profile');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = $this->cache()->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)->with('books', 'profile')
            ->get();

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testMaxModelResultsCreatesCache()
    {
        $authorId = (new Author)->with('books', 'profile')
            ->max('id');
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-books-profile-max_id');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = $this->cache()->tags($tags)
            ->get($key)['value'];
        $liveResult = (new UncachedAuthor)->with('books', 'profile')
            ->max('id');

        $this->assertEquals($authorId, $cachedResult);
        $this->assertEquals($liveResult, $cachedResult);
    }

    public function testMinModelResultsCreatesCache()
    {
        $authorId = (new Author)->with('books', 'profile')
            ->min('id');
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-books-profile-min_id');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = $this->cache()->tags($tags)
            ->get($key)['value'];
        $liveResult = (new UncachedAuthor)->with('books', 'profile')
            ->min('id');

        $this->assertEquals($authorId, $cachedResult);
        $this->assertEquals($liveResult, $cachedResult);
    }

    public function testPluckModelResultsCreatesCache()
    {
        $authors = (new Author)->with('books', 'profile')
            ->pluck('name', 'id');
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor_name-books-profile-pluck_name_id');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = $this->cache()->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)->with('books', 'profile')
            ->pluck('name', 'id');

        $this->assertEmpty($authors->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testSumModelResultsCreatesCache()
    {
        $authorId = (new Author)->with('books', 'profile')
            ->sum('id');
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-books-profile-sum_id');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = $this->cache()->tags($tags)
            ->get($key)['value'];
        $liveResult = (new UncachedAuthor)->with('books', 'profile')
            ->sum('id');

        $this->assertEquals($authorId, $cachedResult);
        $this->assertEquals($liveResult, $cachedResult);
    }

    public function testValueModelResultsCreatesCache()
    {
        $authorName = (new Author)->with('books', 'profile')
            ->value('name');
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-books-profile-value_name');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = $this->cache()->tags($tags)
            ->get($key)['value'];
        $liveResult = (new UncachedAuthor)->with('books', 'profile')
            ->value('name');

        $this->assertEquals($authorName, $cachedResult);
        $this->assertEquals($authorName, $liveResult);
    }

    public function testNestedRelationshipEagerLoading()
    {
        $authors = collect([(new Author)->with('books.publisher')
                ->first()]);

        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-books-books.publisher-first');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturespublisher',
        ];

        $cachedResults = collect([$this->cache()->tags($tags)
                ->get($key)['value']]);
        $liveResults = collect([(new UncachedAuthor)->with('books.publisher')
                ->first()]);

        $this->assertEmpty($authors->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testLazyLoadedRelationshipResolvesThroughCachedBuilder()
    {
        $books = (new Author)->first()->books;
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook-books.author_id_=_1-books.author_id_notnull');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)->first()->books;

        $this->assertEmpty($books->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testLazyLoadingOnResourceIsCached()
    {
        $books = (new AuthorResource((new Author)->first()))->books;
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook-books.author_id_=_1-books.author_id_notnull');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)->first()->books;

        $this->assertEmpty($books->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testOrderByClauseParsing()
    {
        $authors = (new Author)->orderBy('name')->get();

        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor_orderBy_name_asc');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)->orderBy('name')->get();

        $this->assertEmpty($authors->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testNestedRelationshipWhereClauseParsing()
    {
        $authors = (new Author)
            ->with('books.publisher')
            ->get();

        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-books-books.publisher');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturespublisher',
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];

        $liveResults = (new UncachedAuthor)->with('books.publisher')
            ->get();

        $this->assertEmpty($authors->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testExistsRelationshipWhereClauseParsing()
    {
        $authors = (new Author)->whereHas('books')
            ->get();

        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-exists-and_authors.id_=_books.author_id');
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)->whereHas('books')
            ->get();

        $this->assertEmpty($authors->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testDoesntHaveWhereClauseParsing()
    {
        $authors = (new Author)
            ->doesntHave('books')
            ->get();

        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor_notexists_and_authors.id_=_books.author_id');
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->doesntHave('books')
            ->get();

        $this->assertEmpty($authors->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function testScopeClauseParsing()
    {
        $author = factory(Author::class, 1)
            ->create(['name' => 'Anton'])
            ->first();
        $authors = (new Author)
            ->startsWithA()
            ->get();
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-name_like_A%');
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
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
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook-books.author_id_=_1-books.author_id_notnull');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook'
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->first()
            ->books()
            ->get();

        $this->assertTrue($cachedResults->diffKeys($books)->isEmpty());
        $this->assertTrue($liveResults->diffKeys($books)->isEmpty());
    }

    public function testRawOrderByWithoutColumnReference()
    {
        $authors = (new Author)
            ->orderByRaw('DATE()')
            ->get();

        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor_orderByRaw_date');
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor'];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];

        $liveResults = (new UncachedAuthor)
            ->orderByRaw('DATE()')
            ->get();

        $this->assertTrue($cachedResults->diffKeys($authors)->isEmpty());
        $this->assertTrue($liveResults->diffKeys($authors)->isEmpty());
    }

    public function testDelete()
    {
        $author = (new Author)
            ->first();
        $liveResult = (new UncachedAuthor)
            ->first();
        $authorId = $author->id;
        $liveResultId = $liveResult->id;
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor'];

        $author->delete();
        $liveResult->delete();
        $cachedResult = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $deletedAuthor = (new Author)->find($authorId);

        $this->assertEquals($liveResultId, $authorId);
        $this->assertNull($cachedResult);
        $this->assertNull($deletedAuthor);
    }

    public function testWhereBetweenIdsResults()
    {
        $books = (new Book)
            ->whereBetween('price', [5, 10])
            ->get();
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook-price_between_5_10');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->whereBetween('price', [5, 10])
            ->get();

        $this->assertTrue($cachedResults->diffKeys($books)->isEmpty());
        $this->assertTrue($liveResults->diffKeys($books)->isEmpty());
    }

    public function testWhereBetweenDatesResults()
    {
        $books = (new Book)
            ->whereBetween('created_at', ['2018-01-01', '2018-12-31'])
            ->get();
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook-created_at_between_2018-01-01_2018-12-31');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->whereBetween('price', [5, 10])
            ->get();

        $this->assertTrue($cachedResults->diffKeys($books)->isEmpty());
        $this->assertTrue($liveResults->diffKeys($books)->isEmpty());
    }

    public function testWhereDatesResults()
    {
        $books = (new Book)
            ->whereDate('created_at', '>=', '2018-01-01')
            ->get();
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook-created_at_>=_2018-01-01');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->whereBetween('price', [5, 10])
            ->get();

        $this->assertTrue($cachedResults->diffKeys($books)->isEmpty());
        $this->assertTrue($liveResults->diffKeys($books)->isEmpty());
    }

    public function testWhereNotInResults()
    {
        $books = (new Book)
            ->whereNotIn('id', [1, 2])
            ->get();
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook-id_notin_1_2');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->whereNotIn('id', [1, 2])
            ->get();

        $this->assertTrue($cachedResults->diffKeys($books)->isEmpty());
        $this->assertTrue($liveResults->diffKeys($books)->isEmpty());
    }

    public function testHashCollision()
    {
        $key1 = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook-id_notin_1_2');
        $tags1 = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesbook'];
        $books = (new Book)
            ->whereNotIn('id', [1, 2])
            ->get();
        $this->cache()->tags($tags1)->flush();

        $authors = (new Author)
            ->disableCache()
            ->get();
        $key2 = 'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor';
        $this->cache()
            ->tags($tags1)
            ->rememberForever(
                $key1,
                function () use ($key2, $authors) {
                    return [
                        'key' => $key2,
                        'value' => $authors,
                    ];
                }
            );
        $cachedBooks = (new Book)
            ->whereNotIn('id', [1, 2])
            ->get();
        $cachedResults = $this->cache()
            ->tags($tags1)
            ->get($key1)['value'];

        $this->assertTrue($cachedResults->keyBy('id')->diffKeys($books->keyBy('id'))->isEmpty());
        $this->assertTrue($cachedResults->diff($authors)->isNotEmpty());
    }

    public function testSubsequentDisabledCacheQueriesDoNotCache()
    {
        (new Author)->disableCache()->get();
        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor'];
        $cachedAuthors1 = $this->cache()
            ->tags($tags)
            ->get($key)['value'];

        (new Author)->disableCache()->get();
        $cachedAuthors2 = $this->cache()
            ->tags($tags)
            ->get($key)['value'];

        $this->assertEmpty($cachedAuthors1);
        $this->assertEmpty($cachedAuthors2);
    }

    public function testSubsequentFindsReturnDifferentModels()
    {
        $author1 = (new Author)->find(1);
        $author2 = (new Author)->find(2);

        $this->assertNotEquals($author1, $author2);
        $this->assertEquals($author1->id, 1);
        $this->assertEquals($author2->id, 2);
    }

    public function testFindOrFailCachesModels()
    {
        $author = (new Author)
            ->findOrFail(1);

        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-find_1');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->findOrFail(1);

        $this->assertEquals($cachedResults->toArray(), $author->toArray());
        $this->assertEquals($liveResults->toArray(), $author->toArray());
    }

    public function testInsertInvalidatesCache()
    {
        $authors = (new Author)
            ->get();

        (new Author)
            ->insert([
                'name' => 'Test Insert',
                'email' => 'none@noemail.com',
            ]);
        $authorsAfterInsert = (new Author)
            ->get();
        $uncachedAuthors = (new UncachedAuthor)
            ->get();

        $this->assertCount(10, $authors);
        $this->assertCount(11, $authorsAfterInsert);
        $this->assertCount(11, $uncachedAuthors);
    }

    public function testUpdateInvalidatesCache()
    {
        $originalAuthor = (new Author)
            ->first();
        $author = (new Author)
            ->first();

        $author->update([
            "name" => "Updated Name",
        ]);
        $authorAfterUpdate = (new Author)
            ->find($author->id);
        $uncachedAuthor = (new UncachedAuthor)
            ->find($author->id);

        $this->assertNotEquals($originalAuthor->name, $authorAfterUpdate->name);
        $this->assertEquals("Updated Name", $authorAfterUpdate->name);
        $this->assertEquals($authorAfterUpdate->name, $uncachedAuthor->name);
    }

    public function testAttachInvalidatesCache()
    {
        $book = (new Book)
            ->find(1);

        $book->stores()->attach(1);
        $cachedBook = (new Book)
            ->find(1);

        $this->assertTrue($book->stores->keyBy('id')->has(1));
    }

    public function testAccessingGetResultsViaArrayIndexDoesNotError()
    {
        $author = (new Author)
            ->where('id', 1)
            ->get()[0];
        $cachedAuthor = (new Author)
            ->where('id', 1)
            ->get()[0];
        $uncachedAuthor = (new UncachedAuthor)
            ->where('id', 1)
            ->get()[0];

        $this->assertEquals(1, $author->id);
        $this->assertEquals($author, $cachedAuthor);
        $this->assertEquals($author->toArray(), $uncachedAuthor->toArray());
    }
}
