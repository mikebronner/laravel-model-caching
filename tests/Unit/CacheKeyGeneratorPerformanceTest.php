<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Unit;

use GeneaLabs\LaravelModelCaching\CacheKeyGenerator;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class CacheKeyGeneratorPerformanceTest extends IntegrationTestCase
{
    public function test_cache_key_generation_performance(): void
    {
        $builder = (new Author)->newQuery()
            ->where('id', '>', 0)
            ->where('name', 'like', '%test%')
            ->orderBy('name')
            ->with('books');

        $iterations = 1000;

        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            CacheKeyGenerator::generate($builder, ['*']);
        }

        $elapsed = microtime(true) - $start;
        $avgMs = ($elapsed / $iterations) * 1000;

        // Cache key generation should complete in under 1ms per call on average.
        // This is a generous threshold to avoid flaky CI failures.
        $this->assertLessThan(1.0, $avgMs, "Average cache key generation time ({$avgMs}ms) exceeds 1ms threshold");
    }

    public function test_clone_based_generation_does_not_accumulate_state(): void
    {
        $builder = (new Author)->newQuery()->where('id', '>', 0);

        // Generate key multiple times — should not accumulate state on original
        $keys = [];
        for ($i = 0; $i < 10; $i++) {
            $keys[] = CacheKeyGenerator::generate($builder, ['*']);
        }

        // All keys should be identical (no state accumulation)
        $this->assertCount(1, array_unique($keys));
    }
}
