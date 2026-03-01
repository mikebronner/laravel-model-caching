<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class DeleteCacheFlushTest extends IntegrationTestCase
{
    private function authorTags(): array
    {
        return [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors",
        ];
    }

    private function bookTags(): array
    {
        return [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:books",
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

    private function populateAuthorCache(): string
    {
        (new Author)->all();

        $key = sha1(
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null"
        );

        $this->assertNotNull(
            $this->cache()->tags($this->authorTags())->get($key)
        );

        return $key;
    }

    // --- delete() tests ---

    public function testDeleteZeroRowsDoesNotFlushCache()
    {
        $key = $this->populateBookCache();

        $result = (new Book)->where("id", 0)->delete();

        $this->assertEquals(0, $result);
        $this->assertNotNull(
            $this->cache()->tags($this->bookTags())->get($key)
        );
    }

    public function testDeleteOneRowFlushesCache()
    {
        $key = $this->populateBookCache();
        $book = (new Book)->first();

        $result = (new Book)->where("id", $book->id)->delete();

        $this->assertEquals(1, $result);
        $this->assertNull(
            $this->cache()->tags($this->bookTags())->get($key)
        );
    }

    public function testDeleteMultipleRowsFlushesCache()
    {
        $key = $this->populateBookCache();

        $result = (new Book)->where("id", ">", 0)->delete();

        $this->assertGreaterThan(1, $result);
        $this->assertNull(
            $this->cache()->tags($this->bookTags())->get($key)
        );
    }

    // --- forceDelete() tests ---

    public function testForceDeleteZeroRowsDoesNotFlushCache()
    {
        $key = $this->populateAuthorCache();

        $result = (new Author)->where("id", 0)->forceDelete();

        $this->assertEquals(0, $result);
        $this->assertNotNull(
            $this->cache()->tags($this->authorTags())->get($key)
        );
    }

    public function testForceDeleteOneRowFlushesCache()
    {
        $key = $this->populateAuthorCache();

        $result = (new Author)->where("id", 1)->forceDelete();

        $this->assertEquals(1, $result);
        $this->assertNull(
            $this->cache()->tags($this->authorTags())->get($key)
        );
    }

    public function testForceDeleteMultipleRowsFlushesCache()
    {
        $key = $this->populateAuthorCache();

        $result = (new Author)->where("id", ">", 0)->forceDelete();

        $this->assertGreaterThan(1, $result);
        $this->assertNull(
            $this->cache()->tags($this->authorTags())->get($key)
        );
    }

    // --- Integration test ---

    public function testRepeatedDeletesOnEmptyResultSetDoNotFlushCache()
    {
        $key = $this->populateBookCache();

        // Delete with no matching rows multiple times
        for ($i = 0; $i < 3; $i++) {
            $result = (new Book)->where("id", 0)->delete();
            $this->assertEquals(0, $result);
        }

        // Cache should still be intact
        $this->assertNotNull(
            $this->cache()->tags($this->bookTags())->get($key)
        );
    }
}
