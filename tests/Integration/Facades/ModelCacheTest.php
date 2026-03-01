<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\Facades;

use GeneaLabs\LaravelModelCaching\Facades\ModelCache;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use InvalidArgumentException;

class ModelCacheTest extends IntegrationTestCase
{
    public function testInvalidateClearsQueriesForTargetModel()
    {
        $authors = (new Author)->get();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors",
        ];

        $cachedBefore = $this->cache()->tags($tags)->get($key);
        $this->assertNotNull($cachedBefore);

        ModelCache::invalidate(Author::class);

        $cachedAfter = $this->cache()->tags($tags)->get($key);
        $this->assertNull($cachedAfter);
    }

    public function testInvalidatingOneModelDoesNotAffectOtherModelsCache()
    {
        (new Author)->get();
        (new Book)->get();

        $bookTags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:books",
        ];
        $bookKey = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:books:genealabslaravelmodelcachingtestsfixturesbook");

        ModelCache::invalidate(Author::class);

        $bookCache = $this->cache()->tags($bookTags)->get($bookKey);
        $this->assertNotNull($bookCache);
    }

    public function testMultipleModelsCanBeInvalidatedInSingleCall()
    {
        (new Author)->get();
        (new Book)->get();

        $authorTags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors",
        ];
        $authorKey = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null");
        $bookTags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:books",
        ];
        $bookKey = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:books:genealabslaravelmodelcachingtestsfixturesbook");

        ModelCache::invalidate([Author::class, Book::class]);

        $this->assertNull($this->cache()->tags($authorTags)->get($authorKey));
        $this->assertNull($this->cache()->tags($bookTags)->get($bookKey));
    }

    public function testMassUpdateFollowedByInvalidateReturnsFreshData()
    {
        $initialAuthors = (new Author)->get();

        (new UncachedAuthor)->where('id', '>', 0)->update(['name' => 'UPDATED']);

        $cachedAuthors = (new Author)->get();
        $this->assertNotEquals('UPDATED', $cachedAuthors->first()->name);

        ModelCache::invalidate(Author::class);

        $freshAuthors = (new Author)->get();
        $this->assertEquals('UPDATED', $freshAuthors->first()->name);
    }

    public function testInvalidationWorksAfterQueryUpdate()
    {
        $authors = (new Author)->get();
        $firstName = $authors->first()->name;

        (new UncachedAuthor)->query()->where('id', $authors->first()->id)
            ->update(['name' => 'QUERY_UPDATED']);

        $staleAuthors = (new Author)->get();
        $this->assertEquals($firstName, $staleAuthors->first()->name);

        ModelCache::invalidate(Author::class);

        $freshAuthors = (new Author)->get();
        $this->assertEquals('QUERY_UPDATED', $freshAuthors->first()->name);
    }

    public function testArtisanClearCommandContinuesToWork()
    {
        (new Author)->get();

        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors",
        ];
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null");

        $this->assertNotNull($this->cache()->tags($tags)->get($key));

        $this->artisan('modelCache:clear', ['--model' => Author::class])
            ->assertExitCode(0);

        $this->assertNull($this->cache()->tags($tags)->get($key));
    }

    public function testInvalidateThrowsForNonCachableModel()
    {
        $this->expectException(InvalidArgumentException::class);

        ModelCache::invalidate(UncachedAuthor::class);
    }
}
