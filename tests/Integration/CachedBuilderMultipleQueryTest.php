<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Support\Collection;

class CachedBuilderMultipleQueryTest extends IntegrationTestCase
{
    public function testCallingAllThenFirstQueriesReturnsDifferingResults()
    {
        $allAuthors = (new Author)->all();
        $firstAuthor = (new Author)->first();

        $this->assertNotEquals($allAuthors, $firstAuthor);
        $this->assertInstanceOf(Author::class, $firstAuthor);
        $this->assertInstanceOf(Collection::class, $allAuthors);
    }

    public function testCallingGetThenFirstQueriesReturnsDifferingResults()
    {
        $allAuthors = (new Author)->get();
        $firstAuthor = (new Author)->first();

        $this->assertNotEquals($allAuthors, $firstAuthor);
        $this->assertInstanceOf(Author::class, $firstAuthor);
        $this->assertInstanceOf(Collection::class, $allAuthors);
    }

    public function testUsingDestroyInvalidatesCache()
    {
        $allAuthors = (new Author)->get();
        $firstAuthor = $allAuthors->first();
        (new Author)->destroy($firstAuthor->id);
        $updatedAuthors = (new Author)->get()->keyBy("id");

        $this->assertNotEquals($allAuthors, $updatedAuthors);
        $this->assertTrue($allAuthors->contains($firstAuthor));
        $this->assertFalse($updatedAuthors->contains($firstAuthor));
    }

    public function testAllMethodCacheGetsInvalidated()
    {
        $allAuthors = (new Author)->all();
        $firstAuthor = $allAuthors->first();
        $firstAuthor->delete();
        $updatedAuthors = (new Author)->all();

        $this->assertNotEquals($allAuthors, $updatedAuthors);
        $this->assertTrue($allAuthors->contains($firstAuthor));
        $this->assertFalse($updatedAuthors->contains($firstAuthor));
    }
}
