<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorCachedQueryBuilder;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorQueryBuilder;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorWithCachedCustomBuilder;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorWithCustomBuilder;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class CustomBuilderTest extends IntegrationTestCase
{
    /**
     * AC 1: When caching is disabled (config off), models with a custom builder
     *       defined via static $builder receive that custom builder — not a plain
     *       EloquentBuilder and not a CachedBuilder.
     */
    public function testNonCachableModelUsesCustomBuilder()
    {
        // Disable caching globally so newEloquentBuilder() takes the non-cachable path.
        // Use try/finally so config is always restored even when an assertion fails.
        config(['laravel-model-caching.enabled' => false]);

        try {
            $builder = (new AuthorWithCustomBuilder)->newQuery();

            $this->assertInstanceOf(
                AuthorQueryBuilder::class,
                $builder,
                'Non-cachable model with static $builder must return the custom builder class'
            );
        } finally {
            config(['laravel-model-caching.enabled' => true]);
        }
    }

    /**
     * AC 2: When caching is enabled and the model's custom builder extends
     *       CachedBuilder, the custom CachedBuilder subclass is returned so
     *       both custom query methods AND caching are available.
     */
    public function testCachableModelWithCachedBuilderSubclassUsesCustomBuilder()
    {
        $builder = (new AuthorWithCachedCustomBuilder)->newQuery();

        $this->assertInstanceOf(
            AuthorCachedQueryBuilder::class,
            $builder,
            'Cachable model whose custom builder extends CachedBuilder should return that builder'
        );

        // Custom method must be callable on the returned builder
        $this->assertTrue(
            method_exists($builder, 'famous'),
            'Custom query method famous() must be available on the returned builder'
        );
    }

    /**
     * AC 2 continued: the custom CachedBuilder subclass must still cache queries.
     */
    public function testCustomCachedBuilderSubclassStillCachesResults()
    {
        // Warm the cache
        $results = (new AuthorWithCachedCustomBuilder)->get();

        $cacheKey = sha1(
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite" .
            ":authors:genealabslaravelmodelcachingtestsfixturesauthorwithcachedcustombuilder" .
            "-authors.deleted_at_null"
        );
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite" .
            ":genealabslaravelmodelcachingtestsfixturesauthorwithcachedcustombuilder",
        ];

        $cached = $this->cache()->tags($tags)->get($cacheKey);

        $this->assertNotNull($cached, 'Custom CachedBuilder subclass must store results in cache');
        $this->assertEquals(
            $results->count(),
            $cached['value']->count(),
            'Cached count must match the live result count'
        );
    }

    /**
     * AC 3: When caching is enabled but the model's custom builder does NOT extend
     *       CachedBuilder, the package falls back to CachedBuilder so caching is
     *       never silently dropped.
     */
    public function testCachableModelWithNonCachedBuilderFallsBackToCachedBuilder()
    {
        $builder = (new AuthorWithCustomBuilder)->newQuery();

        $this->assertInstanceOf(
            CachedBuilder::class,
            $builder,
            'When custom builder does not extend CachedBuilder, newQuery() must return CachedBuilder'
        );

        // The non-CachedBuilder custom builder must NOT be returned for a cachable model
        $this->assertNotInstanceOf(
            AuthorQueryBuilder::class,
            $builder,
            'CachedBuilder fallback must not be the plain custom builder'
        );
    }

    /**
     * AC 3 continued: the CachedBuilder fallback must still produce correct results.
     */
    public function testCachedBuilderFallbackStillCachesResults()
    {
        $results = (new AuthorWithCustomBuilder)->get();

        $cacheKey = sha1(
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite" .
            ":authors:genealabslaravelmodelcachingtestsfixturesauthorwithcustombuilder" .
            "-authors.deleted_at_null"
        );
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite" .
            ":genealabslaravelmodelcachingtestsfixturesauthorwithcustombuilder",
        ];

        $cached = $this->cache()->tags($tags)->get($cacheKey);

        $this->assertNotNull($cached, 'CachedBuilder fallback must store results in cache');
        $this->assertEquals(
            $results->count(),
            $cached['value']->count()
        );
    }
}
