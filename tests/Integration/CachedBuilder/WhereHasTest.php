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

class WhereHasTest extends IntegrationTestCase
{
    public function testWhereHasClause()
    {
        $authors = (new Author)
            ->whereHas("books")
            ->get();
        $uncachedAuthors = (new UncachedAuthor)
            ->whereHas("books")
            ->get();

        $this->assertEquals($authors->pluck("id"), $uncachedAuthors->pluck("id"));
    }

    public function testNestedWhereHasClauses()
    {
        $authors = (new Author)
            ->where("id", ">", 0)
            ->whereHas("books", function ($query) {
                $query->whereNull("description");
            })
            ->get();
        $uncachedAuthors = (new UncachedAuthor)
            ->where("id", ">", 0)
            ->whereHas("books", function ($query) {
                $query->whereNull("description");
            })
            ->get();

        $this->assertEquals($authors->pluck("id"), $uncachedAuthors->pluck("id"));
    }
}
