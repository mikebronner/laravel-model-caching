<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class FirstTest extends IntegrationTestCase
{
    public function test_first_returns_all_attributes_for_model()
    {
        $author = (new Author)
            ->where('id', '=', 1)
            ->first();
        $uncachedAuthor = (new UncachedAuthor)
            ->where('id', '=', 1)
            ->first();

        $this->assertEquals($author->id, $uncachedAuthor->id);
        $this->assertEquals($author->created_at, $uncachedAuthor->created_at);
        $this->assertEquals($author->updated_at, $uncachedAuthor->updated_at);
        $this->assertEquals($author->email, $uncachedAuthor->email);
        $this->assertEquals($author->name, $uncachedAuthor->name);
    }

    public function test_first_is_not_the_same_as_all()
    {
        $authors = (new Author)
            ->all();
        $author = (new Author)
            ->first();

        $this->assertNotEquals($authors, $author);
    }
}
