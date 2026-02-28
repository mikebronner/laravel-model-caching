<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Unit;

use GeneaLabs\LaravelModelCaching\CacheKeyGenerator;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class CacheKeyGeneratorTest extends IntegrationTestCase
{
    public function testOriginalBuilderIsNotMutatedDuringKeyGeneration(): void
    {
        $builder = (new Author)->newQuery()->where('name', 'Test');

        // Capture original state
        $originalSql = $builder->toSql();
        $originalBindings = $builder->getBindings();
        $originalEagerLoads = $builder->getEagerLoads();

        // Generate cache key (should use a clone internally)
        $key = CacheKeyGenerator::generate($builder, ['*']);

        // Verify original builder was not mutated
        $this->assertSame($originalSql, $builder->toSql());
        $this->assertSame($originalBindings, $builder->getBindings());
        $this->assertSame($originalEagerLoads, $builder->getEagerLoads());
        $this->assertNotEmpty($key);
    }

    public function testCloneUsedForKeyGenerationIsDistinctFromOriginal(): void
    {
        $builder = (new Author)->newQuery()->where('id', '>', 5);

        // Generate two cache keys — should be identical for the same builder state
        $key1 = CacheKeyGenerator::generate($builder, ['*']);
        $key2 = CacheKeyGenerator::generate($builder, ['*']);

        $this->assertSame($key1, $key2);

        // Modify the builder and verify the key changes
        $builder->where('name', 'like', '%test%');
        $key3 = CacheKeyGenerator::generate($builder, ['*']);

        $this->assertNotSame($key1, $key3);
    }

    public function testBuilderWithEagerLoadsGeneratesConsistentKeys(): void
    {
        $builder = (new Author)->newQuery()->with('books');

        $key1 = CacheKeyGenerator::generate($builder, ['*']);
        $key2 = CacheKeyGenerator::generate($builder, ['*']);

        $this->assertSame($key1, $key2);
        $this->assertNotEmpty($key1);
    }

    public function testKeyDifferentiatorChangesKey(): void
    {
        $builder = (new Author)->newQuery();

        $keyGet = CacheKeyGenerator::generate($builder, ['*'], null, '');
        $keyCount = CacheKeyGenerator::generate($builder, ['*'], null, '-count');
        $keyFirst = CacheKeyGenerator::generate($builder, ['*'], null, '-first');

        $this->assertNotSame($keyGet, $keyCount);
        $this->assertNotSame($keyGet, $keyFirst);
        $this->assertNotSame($keyCount, $keyFirst);
    }

    public function testColumnsAffectCacheKey(): void
    {
        $builder = (new Author)->newQuery();

        $keyAll = CacheKeyGenerator::generate($builder, ['*']);
        $keySpecific = CacheKeyGenerator::generate($builder, ['id', 'name']);

        $this->assertNotSame($keyAll, $keySpecific);
    }

    public function testOriginalBuilderScopesAreNotAppliedAfterKeyGeneration(): void
    {
        $builder = (new Author)->newQuery();

        // The scopesAreApplied flag should not change on the original
        $metadata = $builder->getCacheKeyMetadata();
        $originalScopesApplied = $metadata['scopesAreApplied'];

        CacheKeyGenerator::generate($builder, ['*']);

        $metadataAfter = $builder->getCacheKeyMetadata();
        $this->assertSame($originalScopesApplied, $metadataAfter['scopesAreApplied']);
    }
}
