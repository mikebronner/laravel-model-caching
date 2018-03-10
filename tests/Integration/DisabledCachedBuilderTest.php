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
class DisabledCachedBuilderTest extends IntegrationTestCase
{
    use RefreshDatabase;

    public function testAvgModelResultsIsNotCached()
    {
        $authorId = (new Author)
            ->with('books', 'profile')
            ->disableCache()
            ->avg('id');
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile-avg_id');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = $this->cache()
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
                $cachedChunks['keys']->push(sha1(
                    "genealabslaravelmodelcachingtestsfixturesauthor-books-pr" .
                    "ofile_orderBy_authors.id_asc{$offset}-limit_3"
                ));
            });

        $liveResults = (new UncachedAuthor)->with('books', 'profile')
            ->chunk($chunkSize, function ($chunk) use (&$uncachedChunks) {
                $uncachedChunks->push($chunk);
            });

        for ($index = 0; $index < $cachedChunks['authors']->count(); $index++) {
            $key = $cachedChunks['keys'][$index];
            $cachedResults = $this->cache()
                ->tags($tags)
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
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile-count');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = $this->cache()
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
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile-cursor');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key);
        $liveResults = collect(
            (new UncachedAuthor)
                ->with('books', 'profile')
                ->cursor()
        );

        $this->assertEmpty($liveResults->diffKeys($authors));
        $this->assertNull($cachedResults);
    }

    public function testFindModelResultsIsNotCached()
    {
        $author = (new Author)
            ->with('books')
            ->disableCache()
            ->find(1);
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor_1');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResult = $this->cache()
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
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)
            ->with('books', 'profile')
            ->get();

        $this->assertEmpty($liveResults->diffKeys($authors));
        $this->assertNull($cachedResults);
    }

    public function testMaxModelResultsIsNotCached()
    {
        $authorId = (new Author)
            ->with('books', 'profile')
            ->disableCache()
            ->max('id');
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile-max_id');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = $this->cache()
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
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile-min_id');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = $this->cache()
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
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor_name-books-profile-pluck_name_id');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)
            ->with('books', 'profile')
            ->pluck('name', 'id');

        $this->assertEmpty($liveResults->diffKeys($authors));
        $this->assertNull($cachedResults);
    }

    public function testSumModelResultsIsNotCached()
    {
        $authorId = (new Author)
            ->with('books', 'profile')
            ->disableCache()
            ->sum('id');
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor-books-profile-sum_id');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = $this->cache()
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
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor_name-books-profile-first');
        $tags = [
            'genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabslaravelmodelcachingtestsfixturesbook',
            'genealabslaravelmodelcachingtestsfixturesprofile',
        ];

        $cachedResult = $this->cache()
            ->tags($tags)
            ->get($key);

        $liveResult = (new UncachedAuthor)
            ->with('books', 'profile')
            ->value('name');

        $this->assertEquals($author, $liveResult);
        $this->assertNull($cachedResult);
    }

    public function testPaginationIsCached()
    {
        $authors = (new Author)
            ->disableCache()
            ->paginate(3);

        $key = sha1('genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor-paginate_by_3_page_1');
        $tags = [
            'genealabs:laravel-model-caching:testing:genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->paginate(3);

        $this->assertNull($cachedResults);
        $this->assertEquals($liveResults->toArray(), $authors->toArray());
    }
}
