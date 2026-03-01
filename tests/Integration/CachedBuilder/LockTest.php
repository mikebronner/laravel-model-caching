<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class LockTest extends IntegrationTestCase
{
    public function testLockForUpdateBypassesCache()
    {
        // Prime the cache
        $cachedAuthors = (new Author)->get();

        // Query with lockForUpdate should bypass cache and hit DB
        $lockedAuthors = (new Author)->lockForUpdate()->get();

        $uncachedAuthors = (new UncachedAuthor)->get();

        $this->assertEmpty($uncachedAuthors->diffKeys($lockedAuthors));
    }

    public function testLockForUpdateDoesNotStoreResultInCache()
    {
        // Flush cache to start clean
        $this->cache()->flush();

        // Run a lockForUpdate query - result should NOT be cached
        $lockedAuthors = (new Author)->lockForUpdate()->get();

        // Build the cache key that would be used for a normal (non-locked) query
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $cachedResult = $this->cache()->tags($tags)->get($key);

        $this->assertNull($cachedResult);
    }

    public function testSharedLockBypassesCache()
    {
        // Prime the cache
        $cachedAuthors = (new Author)->get();

        // Query with sharedLock should bypass cache and hit DB
        $lockedAuthors = (new Author)->sharedLock()->get();

        $uncachedAuthors = (new UncachedAuthor)->get();

        $this->assertEmpty($uncachedAuthors->diffKeys($lockedAuthors));
    }

    public function testSharedLockDoesNotStoreResultInCache()
    {
        $this->cache()->flush();

        $lockedAuthors = (new Author)->sharedLock()->get();

        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $cachedResult = $this->cache()->tags($tags)->get($key);

        $this->assertNull($cachedResult);
    }
}
