<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class DeleteTest extends IntegrationTestCase
{
    public function testDecrementingInvalidatesCache()
    {
        $book = (new Book)
            ->orderBy("id", "DESC")
            ->first();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:books:genealabslaravelmodelcachingtestsfixturesbook_orderBy_id_desc-first");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook",
        ];

        $beforeDeleteCachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $book->delete();
        $afterDeleteCachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value']
            ?? null;

        $this->assertEquals($beforeDeleteCachedResults->id, $book->id);
        $this->assertNotEquals($beforeDeleteCachedResults, $afterDeleteCachedResults);
        $this->assertNull($afterDeleteCachedResults);
    }
}
