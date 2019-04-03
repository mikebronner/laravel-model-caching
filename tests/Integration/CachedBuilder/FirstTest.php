<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

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

class FirstTest extends IntegrationTestCase
{
    public function testFirstReturnsAllAttributesForModel()
    {
        $author = (new Author)
            ->where("id", "=", 1)
            ->first();
        $uncachedAuthor = (new UncachedAuthor)
            ->where("id", "=", 1)
            ->first();

        $this->assertEquals($author->id, $uncachedAuthor->id);
        $this->assertEquals($author->created_at, $uncachedAuthor->created_at);
        $this->assertEquals($author->updated_at, $uncachedAuthor->updated_at);
        $this->assertEquals($author->email, $uncachedAuthor->email);
        $this->assertEquals($author->name, $uncachedAuthor->name);
    }

    public function testFirstIsNotTheSameAsAll()
    {
        $authors = (new Author)
            ->all();
        $author = (new Author)
            ->first();

        $this->assertNotEquals($authors, $author);
    }
}
