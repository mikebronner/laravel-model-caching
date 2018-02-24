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
use GeneaLabs\LaravelModelCaching\Tests\UnitTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CachedModelTest extends UnitTestCase
{
    use RefreshDatabase;

    public function testAllModelResultsCreatesCache()
    {
        $authors = (new Author)->all();
        $key = sha1('genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = [
            'genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->all();

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($liveResults->diffAssoc($cachedResults));
    }

    public function testScopeDisablesCaching()
    {
        $key = sha1('genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor'];
        $authors = (new Author)
            ->where("name", "Bruno")
            ->disableCache()
            ->get();

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];

        $this->assertNull($cachedResults);
        $this->assertNotEquals($authors, $cachedResults);
    }

    public function testAllMethodCachingCanBeDisabledViaConfig()
    {
        config(['laravel-model-caching.disabled' => true]);
        $authors = (new Author)
            ->all();
        $key = sha1('genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = [
            'genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor',
        ];
        config(['laravel-model-caching.disabled' => false]);

        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];

        $this->assertEmpty($cachedResults);
        $this->assertNotEmpty($authors);
        $this->assertCount(10, $authors);
    }

    public function testWhereHasIsBeingCached()
    {
        $books = (new Book)
            ->with('author')
            ->whereHas('author', function ($query) {
                $query->whereId('1');
            })
            ->get();

        $key = sha1('genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesbook_exists_and_books.author_id_=_authors.id-id_=_1-author');
        $tags = [
            'genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:genealabslaravelmodelcachingtestsfixturesauthor',
        ];

        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];

        $this->assertEquals(1, $books->first()->author->id);
        $this->assertEquals(1, $cachedResults->first()->author->id);
    }
}
