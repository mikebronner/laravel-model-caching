<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\Traits;

use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorWithConflictingTrait;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorWithMergedTraits;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class TraitCollisionTest extends IntegrationTestCase
{
    public function testModelWithConflictingTraitLoadsWithoutFatalError()
    {
        $author = new AuthorWithConflictingTrait;

        $this->assertInstanceOf(AuthorWithConflictingTrait::class, $author);
    }

    public function testModelWithConflictingTraitReturnsCachedBuilder()
    {
        $query = (new AuthorWithConflictingTrait)->query();

        $this->assertInstanceOf(CachedBuilder::class, $query);
    }

    public function testModelWithConflictingTraitCachesResults()
    {
        $authors = AuthorWithConflictingTrait::all();
        $cachedAuthors = AuthorWithConflictingTrait::all();

        $this->assertEquals($authors->count(), $cachedAuthors->count());
    }

    public function testModelWithMergedTraitsLoadsWithoutFatalError()
    {
        $author = new AuthorWithMergedTraits;

        $this->assertInstanceOf(AuthorWithMergedTraits::class, $author);
    }

    public function testModelWithMergedTraitsReturnsCachedBuilder()
    {
        $query = (new AuthorWithMergedTraits)->query();

        $this->assertInstanceOf(CachedBuilder::class, $query);
    }

    public function testModelWithMergedTraitsCachesResults()
    {
        $authors = AuthorWithMergedTraits::all();
        $cachedAuthors = AuthorWithMergedTraits::all();

        $this->assertEquals($authors->count(), $cachedAuthors->count());
    }

    public function testNewModelCachingEloquentBuilderHelperIsAccessible()
    {
        $author = new AuthorWithConflictingTrait;
        $query = $author->newQuery()->getQuery();
        $builder = $author->newModelCachingEloquentBuilder($query);

        $this->assertInstanceOf(CachedBuilder::class, $builder);
    }
}
