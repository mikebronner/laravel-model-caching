<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

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
