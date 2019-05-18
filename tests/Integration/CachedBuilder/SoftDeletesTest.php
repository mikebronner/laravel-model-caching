<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class SoftDeletesTest extends IntegrationTestCase
{
    public function testWithTrashedIsCached()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.id_=_1-first');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor',
        ];
        $author = (new UncachedAuthor)
            ->first();
        $author->delete();

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

    // public function testWithoutTrashedIsCached()
    // {
    //     $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor_1-first');
    //     $tags = [
    //         'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor',
    //     ];
    //     $author = (new UncachedAuthor)
    //         ->first();
    //     $author->delete();

    //     $deletedAuthor = (new Author)
    //         ->first($author->id);
    //     $cachedResults = $this
    //         ->cache()
    //         ->tags($tags)
    //         ->get($key)['value'];
    //     $deletedUncachedAuthor = (new UncachedAuthor)
    //         ->first($author->id);

    //     $this->assertEquals($cachedResults->toArray(), $deletedAuthor->toArray());
    //     $this->assertEquals($cachedResults->toArray(), $deletedUncachedAuthor->toArray());
    // }
}
