<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class SoftDeletesTest extends IntegrationTestCase
{
    public function testWithTrashedIsCached()
    {
        $author = (new UncachedAuthor)
            ->first();
        $author->delete();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-find_1-withTrashed");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $deletedAuthor = (new Author)
            ->withTrashed()
            ->find($author->id);
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $deletedUncachedAuthor = (new UncachedAuthor)
            ->withTrashed()
            ->find($author->id);

        $this->assertEquals($cachedResults->toArray(), $deletedAuthor->toArray());
        $this->assertEquals($cachedResults->toArray(), $deletedUncachedAuthor->toArray());
    }

    public function testWithoutTrashedIsCached()
    {
        $author = (new UncachedAuthor)
            ->first();
        $author->delete();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null-find_{$author->id}-withoutTrashed");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $result = (new Author)
            ->withoutTrashed()
            ->find($author->id);
        $cachedResult = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $uncachedResult = (new UncachedAuthor)
            ->withoutTrashed()
            ->find($author->id);

        $this->assertEquals($uncachedResult, $result);
        $this->assertEquals($uncachedResult, $cachedResult);
        $this->assertNull($result);
        $this->assertNull($cachedResult);
        $this->assertNull($uncachedResult);
    }

    public function testonlyTrashedIsCached()
    {
        $author = (new UncachedAuthor)
            ->first();
        $author->delete();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_notnull-find_{$author->id}-onlyTrashed");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $deletedAuthor = (new Author)
            ->onlyTrashed()
            ->find($author->id);
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $deletedUncachedAuthor = (new UncachedAuthor)
            ->onlyTrashed()
            ->find($author->id);

        $this->assertEquals($cachedResults->toArray(), $deletedAuthor->toArray());
        $this->assertEquals($cachedResults->toArray(), $deletedUncachedAuthor->toArray());
    }
}
