<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Support\Collection;

class CachedBuilderMultipleQueryTest extends IntegrationTestCase
{
    public function test_calling_all_then_first_queries_returns_differing_results()
    {
        $allAuthors = (new Author)->all();
        $firstAuthor = (new Author)->first();

        $this->assertNotEquals($allAuthors, $firstAuthor);
        $this->assertInstanceOf(Author::class, $firstAuthor);
        $this->assertInstanceOf(Collection::class, $allAuthors);
    }

    public function test_calling_get_then_first_queries_returns_differing_results()
    {
        $allAuthors = (new Author)->get();
        $firstAuthor = (new Author)->first();

        $this->assertNotEquals($allAuthors, $firstAuthor);
        $this->assertInstanceOf(Author::class, $firstAuthor);
        $this->assertInstanceOf(Collection::class, $allAuthors);
    }

    public function test_using_destroy_invalidates_cache()
    {
        $allAuthors = (new Author)->get();
        $firstAuthor = $allAuthors->first();
        (new Author)->destroy($firstAuthor->id);
        $updatedAuthors = (new Author)->get()->keyBy('id');

        $this->assertNotEquals($allAuthors, $updatedAuthors);
        $this->assertTrue($allAuthors->contains($firstAuthor));
        $this->assertFalse($updatedAuthors->contains($firstAuthor));
    }

    public function test_all_method_cache_gets_invalidated()
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
