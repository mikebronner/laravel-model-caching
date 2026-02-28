<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class DestroyCacheFlushTest extends IntegrationTestCase
{
    private function bookTags(): array
    {
        return [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook",
        ];
    }

    private function populateBookCache(): string
    {
        (new Book)->all();

        $key = sha1(
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:books:genealabslaravelmodelcachingtestsfixturesbook"
        );

        $this->assertNotNull(
            $this->cache()->tags($this->bookTags())->get($key)
        );

        return $key;
    }

    public function testDestroyWithNonExistentIdsDoesNotFlushCache()
    {
        $key = $this->populateBookCache();

        $result = Book::destroy([999998, 999999]);

        $this->assertEquals(0, $result);
        $this->assertNotNull(
            $this->cache()->tags($this->bookTags())->get($key)
        );
    }

    public function testDestroyWithExistingIdFlushesCache()
    {
        $key = $this->populateBookCache();
        $book = (new Book)->first();

        $result = Book::destroy($book->id);

        $this->assertEquals(1, $result);
        $this->assertNull(
            $this->cache()->tags($this->bookTags())->get($key)
        );
    }

    public function testDestroyWithMultipleExistingIdsFlushesCache()
    {
        $key = $this->populateBookCache();
        $bookIds = (new Book)->take(3)->pluck('id')->toArray();

        $result = Book::destroy($bookIds);

        $this->assertEquals(3, $result);
        $this->assertNull(
            $this->cache()->tags($this->bookTags())->get($key)
        );
    }
}
