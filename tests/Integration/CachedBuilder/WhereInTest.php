<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class WhereInTest extends IntegrationTestCase
{
    public function testWhereInUsingCollectionQuery()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::books:genealabslaravelmodelcachingtestsfixturesbook-author_id_in_1_2_3_4');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook',
        ];
        $authors = (new UncachedAuthor)
            ->where("id", "<", 5)
            ->get(["id"]);

        $books = (new Book)
            ->whereIn("author_id", $authors)
            ->get();
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedBook)
            ->whereIn("author_id", $authors)
            ->get();

        $this->assertEquals($liveResults->pluck("id"), $books->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
    }

    /** @group test */
    public function testWhereInWhenSetIsEmpty()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::books:genealabslaravelmodelcachingtestsfixturesbook-author_id_in_1_2_3_4');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook',
        ];
        $authors = (new UncachedAuthor)
            ->where("id", "<", 0)
            ->get(["id"]);

        $books = (new Book)
            ->whereIn("author_id", $authors)
            ->get();
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedBook)
            ->whereIn("author_id", $authors)
            ->get();

        $this->assertEquals($liveResults->pluck("id"), $books->pluck("id"));
        $this->assertNull($cachedResults);
    }
}
