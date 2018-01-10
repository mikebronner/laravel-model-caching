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
class DisabledCachedBuilderTest extends UnitTestCase
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

    public function testAvgModelResultsIsNotCached()
    {
        $authorId = (new Author)
            ->with('books', 'profile')
            ->disableCache()
            ->avg('id');
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-books-profile-avg_id';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = cache()
            ->tags($tags)
            ->get($key);
        $liveResult = (new UncachedAuthor)
            ->with('books', 'profile')
            ->avg('id');

        $this->assertEquals($authorId, $liveResult);
        $this->assertNull($cachedResult);
    }

    public function testChunkModelResultsIsNotCached()
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

        $authors = (new Author)->with('books', 'profile')
            ->disableCache()
            ->chunk($chunkSize, function ($chunk) use (&$cachedChunks, $chunkSize) {
                $offset = '';

                if ($cachedChunks['authors']->count()) {
                    $offsetIncrement = $cachedChunks['authors']->count() * $chunkSize;
                    $offset = "-offset_{$offsetIncrement}";
                }

                $cachedChunks['authors']->push($chunk);
                $cachedChunks['keys']->push("genealabslaravelmodelcachingtestsfixturesauthor-books-profile_orderBy_authors.id_asc{$offset}-limit_3");
            });

        $liveResults = (new UncachedAuthor)->with('books', 'profile')
            ->chunk($chunkSize, function ($chunk) use (&$uncachedChunks) {
                $uncachedChunks->push($chunk);
            });

        for ($index = 0; $index < $cachedChunks['authors']->count(); $index++) {
            $key = $cachedChunks['keys'][$index];
            $cachedResults = cache()->tags($tags)
                ->get($key);

            $this->assertNull($cachedResults);
            $this->assertEquals($authors, $liveResults);
        }
    }

    public function testCountModelResultsIsNotCached()
    {
        $authors = (new Author)
            ->with('books', 'profile')
            ->disableCache()
            ->count();
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-books-profile-count';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = cache()
            ->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)
            ->with('books', 'profile')
            ->count();

        $this->assertEquals($authors, $liveResults);
        $this->assertNull($cachedResults);
    }

    public function testCursorModelResultsIsNotCached()
    {
        $authors = (new Author)
            ->with('books', 'profile')
            ->disableCache()
            ->cursor();
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-books-profile-cursor';
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

        $this->assertEmpty($liveResults->diffAssoc($authors));
        $this->assertNull($cachedResults);
    }

    public function testFindModelResultsIsNotCached()
    {
        $author = (new Author)
            ->with('books')
            ->disableCache()
            ->find(1);
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor_1';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResult = cache()
            ->tags($tags)
            ->get($key);
        $liveResult = (new UncachedAuthor)
            ->find(1);

        $this->assertEquals($liveResult->name, $author->name);
        $this->assertNull($cachedResult);
    }

    public function testGetModelResultsIsNotCached()
    {
        $authors = (new Author)
            ->with('books', 'profile')
            ->disableCache()
            ->get();
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-books-profile';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = cache()
            ->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)
            ->with('books', 'profile')
            ->get();

        $this->assertEmpty($liveResults->diffAssoc($authors));
        $this->assertNull($cachedResults);
    }

    public function testMaxModelResultsIsNotCached()
    {
        $authorId = (new Author)
            ->with('books', 'profile')
            ->disableCache()
            ->max('id');
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-books-profile-max_id';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = cache()
            ->tags($tags)
            ->get($key);
        $liveResult = (new UncachedAuthor)
            ->with('books', 'profile')
            ->max('id');

        $this->assertEquals($authorId, $liveResult);
        $this->assertNull($cachedResult);
    }

    public function testMinModelResultsIsNotCached()
    {
        $authorId = (new Author)
            ->with('books', 'profile')
            ->disableCache()
            ->min('id');
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-books-profile-min_id';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = cache()
            ->tags($tags)
            ->get($key);
        $liveResult = (new UncachedAuthor)
            ->with('books', 'profile')
            ->min('id');

        $this->assertEquals($authorId, $liveResult);
        $this->assertNull($cachedResult);
    }

    public function testPluckModelResultsIsNotCached()
    {
        $authors = (new Author)
            ->with('books', 'profile')
            ->disableCache()
            ->pluck('name', 'id');
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor_name-books-profile-pluck_name_id';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = cache()
            ->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)
            ->with('books', 'profile')
            ->pluck('name', 'id');

        $this->assertEmpty($liveResults->diffAssoc($authors));
        $this->assertNull($cachedResults);
    }

    public function testSumModelResultsIsNotCached()
    {
        $authorId = (new Author)
            ->with('books', 'profile')
            ->disableCache()
            ->sum('id');
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor-books-profile-sum_id';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = cache()
            ->tags($tags)
            ->get($key);
        $liveResult = (new UncachedAuthor)
            ->with('books', 'profile')
            ->sum('id');

        $this->assertEquals($authorId, $liveResult);
        $this->assertNull($cachedResult);
    }

    public function testValueModelResultsIsNotCached()
    {
        $author = (new Author)
            ->with('books', 'profile')
            ->disableCache()
            ->value('name');
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor_name-books-profile-first';
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = cache()
            ->tags($tags)
            ->get($key);

        $liveResult = (new UncachedAuthor)
            ->with('books', 'profile')
            ->value('name');

        $this->assertEquals($author, $liveResult);
        $this->assertNull($cachedResult);
    }
}
