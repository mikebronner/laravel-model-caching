<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Profile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedProfile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPublisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedStore;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Http\Resources\Author as AuthorResource;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
