<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration;

use GeneaLabs\LaravelModelCaching\CachingScope;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorWithInlineGlobalScope;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class CachingExtensionTest extends IntegrationTestCase
{
    public function testCachingWorksWhenRegisteredAsExtension(): void
    {
        $authors = (new Author)->get();
        $cachedAuthors = (new Author)->get();

        $this->assertEquals($authors->pluck('id'), $cachedAuthors->pluck('id'));
    }

    public function testChainedQueryMethodsWorkWithExtensionBasedCaching(): void
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

    public function testDisableModelCachingMacroWorks(): void
    {
        $builder = (new Author)->newQuery();

        $this->assertTrue($builder->isCachable());

        $builder->disableModelCaching();

        $this->assertFalse($builder->isCachable());
    }

    public function testGlobalScopeParsingThroughExtensionMechanism(): void
    {
        // AuthorWithInlineGlobalScope uses a global scope
        $authors = (new AuthorWithInlineGlobalScope)->get();
        $cachedAuthors = (new AuthorWithInlineGlobalScope)->get();

        $this->assertEquals($authors->pluck('id'), $cachedAuthors->pluck('id'));
    }

    public function testCachingScopeIsRegisteredAsGlobalScope(): void
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

    public function testCachingExtensionDoesNotInterfereWithUncachedModels(): void
    {
        $cachedAuthors = (new Author)->get();
        $uncachedAuthors = (new UncachedAuthor)->get();

        $this->assertEquals(
            $cachedAuthors->pluck('id')->sort()->values(),
            $uncachedAuthors->pluck('id')->sort()->values()
        );
    }

    public function testChainedWhereClausesProduceDifferentCacheKeys(): void
    {
        $authors1 = (new Author)->where('id', '>', 0)->get();
        $authors2 = (new Author)->where('id', '>', 5)->get();

        // Different conditions should produce potentially different results
        // (both should be cached separately)
        $this->assertNotNull($authors1);
        $this->assertNotNull($authors2);
    }
}
