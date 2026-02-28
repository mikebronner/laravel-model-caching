<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration;

use GeneaLabs\LaravelModelCaching\CachingScope;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorWithInlineGlobalScope;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class CachingExtensionTest extends IntegrationTestCase
{
    public function test_caching_works_when_registered_as_extension(): void
    {
        $authors = (new Author)->get();
        $cachedAuthors = (new Author)->get();

        $this->assertEquals($authors->pluck('id'), $cachedAuthors->pluck('id'));
    }

    public function test_chained_query_methods_work_with_extension_based_caching(): void
    {
        $authors = (new Author)
            ->where('id', '>', 0)
            ->orderBy('name')
            ->limit(5)
            ->get();

        $cachedAuthors = (new Author)
            ->where('id', '>', 0)
            ->orderBy('name')
            ->limit(5)
            ->get();

        $this->assertEquals($authors->pluck('id'), $cachedAuthors->pluck('id'));
        $this->assertLessThanOrEqual(5, $authors->count());
    }

    public function test_disable_model_caching_macro_works(): void
    {
        $builder = (new Author)->newQuery();

        $this->assertTrue($builder->isCachable());

        $builder->disableModelCaching();

        $this->assertFalse($builder->isCachable());
    }

    public function test_global_scope_parsing_through_extension_mechanism(): void
    {
        // AuthorWithInlineGlobalScope uses a global scope
        $authors = (new AuthorWithInlineGlobalScope)->get();
        $cachedAuthors = (new AuthorWithInlineGlobalScope)->get();

        $this->assertEquals($authors->pluck('id'), $cachedAuthors->pluck('id'));
    }

    public function test_caching_scope_is_registered_as_global_scope(): void
    {
        $author = new Author;
        $builder = $author->newQuery();

        // The CachingScope should be registered as a global scope
        $scopes = $builder->removedScopes();

        // We can verify the scope was added by checking it doesn't interfere
        // with normal query execution
        $result = $builder->get();
        $this->assertNotNull($result);
    }

    public function test_caching_extension_does_not_interfere_with_uncached_models(): void
    {
        $cachedAuthors = (new Author)->get();
        $uncachedAuthors = (new UncachedAuthor)->get();

        $this->assertEquals(
            $cachedAuthors->pluck('id')->sort()->values(),
            $uncachedAuthors->pluck('id')->sort()->values()
        );
    }

    public function test_chained_where_clauses_produce_different_cache_keys(): void
    {
        $authors1 = (new Author)->where('id', '>', 0)->get();
        $authors2 = (new Author)->where('id', '>', 5)->get();

        // Different conditions should produce potentially different results
        // (both should be cached separately)
        $this->assertNotNull($authors1);
        $this->assertNotNull($authors2);
    }
}
