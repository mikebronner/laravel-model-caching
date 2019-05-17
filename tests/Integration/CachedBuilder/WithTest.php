<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class WithTest extends IntegrationTestCase
{
    public function testWithQuery()
    {
        $author = (new Author)
            ->where("id", 1)
            ->with([
                'books' => function ($query) {
                    $query->where("id", "<", 100);
                }
            ])
            ->first();
        $uncachedAuthor = (new UncachedAuthor)->with([
                'books' => function ($query) {
                    $query->where("id", "<", 100);
                },
            ])
            ->where("id", 1)
            ->first();

        $this->assertEquals($uncachedAuthor->books()->count(), $author->books()->count());
        $this->assertEquals($uncachedAuthor->id, $author->id);
    }

    public function testMultiLevelWithQuery()
    {
        $author = (new Author)
            ->where("id", 1)
            ->with([
                'books.publisher' => function ($query) {
                    $query->where("id", "<", 100);
                }
            ])
            ->first();
        $uncachedAuthor = (new UncachedAuthor)->with([
                'books.publisher' => function ($query) {
                    $query->where("id", "<", 100);
                },
            ])
            ->where("id", 1)
            ->first();

        $this->assertEquals($uncachedAuthor->books()->count(), $author->books()->count());
        $this->assertEquals($uncachedAuthor->id, $author->id);
    }

    public function testWithBelongsToManyRelationshipQuery()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::test-prefix:books:genealabslaravelmodelcachingtestsfixturesbook-books.id_=_3-testing::memory::stores-first');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::test-prefix:genealabslaravelmodelcachingtestsfixturesbook',
            'genealabs:laravel-model-caching:testing::memory::test-prefix:genealabslaravelmodelcachingtestsfixturesstore',
        ];

        $stores = (new Book)
            ->with("stores")
            ->find(3)
            ->stores;
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value']
            ->stores;
        $liveResults = (new UncachedBook)
            ->with("stores")
            ->find(3)
            ->stores;

        $this->assertEquals($liveResults->pluck("id"), $stores->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
    }
}
