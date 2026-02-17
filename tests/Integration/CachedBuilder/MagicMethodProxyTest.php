<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Database\Eloquent\Builder;

/**
 * Regression tests for issue #391:
 * Call to undefined method GeneaLabs\LaravelModelCaching\CachedBuilder::type()
 *
 * Root cause: magic methods registered as global Builder macros were not being
 * proxied through CachedBuilder, causing BadMethodCallException in PHP 8+ and
 * Lumen environments.
 *
 * Also fixes: global macros were not tracked in macroKey, causing cache key
 * collisions when multiple different global macros were used.
 */
class MagicMethodProxyTest extends IntegrationTestCase
{
    protected function tearDown(): void
    {
        // Remove any macros registered during tests so they don't bleed into
        // other test suites.
        $reflection = new \ReflectionClass(Builder::class);
        $macros = $reflection->getStaticPropertyValue('macros');
        unset($macros['type'], $macros['ofType'], $macros['customFilter']);
        $reflection->setStaticPropertyValue('macros', $macros);

        parent::tearDown();
    }

    /**
     * AC: CachedBuilder magic method proxy does not throw BadMethodCallException
     * for valid builder methods registered as global macros.
     */
    public function testGlobalMacroProxyDoesNotThrowBadMethodCallException(): void
    {
        // Simulates a Lumen 8+ environment where `type()` is registered as a
        // global macro on the Eloquent Builder (e.g., by a search or CMS package).
        Builder::macro('type', function (string $type) {
            /** @var Builder $this */
            return $this->where('name', 'like', "%{$type}%");
        });

        // Use an unusual prefix unlikely to match any seeded data.
        factory(Author::class, 3)->create(['name' => 'ZZZFICTION-UNIQUE-TEST Author']);
        factory(Author::class, 2)->create(['name' => 'ZZZOTHER-UNIQUE-TEST Author']);

        // Must not throw BadMethodCallException
        $fictionAuthors = Author::type('ZZZFICTION-UNIQUE-TEST')->get();

        $this->assertNotEmpty($fictionAuthors);
        $this->assertCount(3, $fictionAuthors);
    }

    /**
     * AC: Global macros produce distinct cache keys — two different macro
     * argument values return different cached results.
     */
    public function testGlobalMacroProducesDistinctCacheKeys(): void
    {
        Builder::macro('type', function (string $type) {
            /** @var Builder $this */
            return $this->where('name', 'like', "%{$type}%");
        });

        factory(Author::class)->create(['name' => 'ZZZFICTION-UNIQUE Author']);
        factory(Author::class)->create(['name' => 'ZZZNONFICTION-UNIQUE Author']);

        $fiction = Author::type('ZZZFICTION-UNIQUE')->get();
        $nonFiction = Author::type('ZZZNONFICTION-UNIQUE')->get();

        $this->assertCount(1, $fiction);
        $this->assertCount(1, $nonFiction);
        $this->assertNotEquals($fiction->first()->id, $nonFiction->first()->id);
    }

    /**
     * AC: Two different global macros produce distinct cache keys
     * (tests the macroKey tracking fix for global macros).
     */
    public function testTwoDifferentGlobalMacrosProduceDistinctCacheKeys(): void
    {
        Builder::macro('ofType', function (string $type) {
            /** @var Builder $this */
            return $this->where('name', 'like', "%{$type}%");
        });

        Builder::macro('customFilter', function (string $value) {
            /** @var Builder $this */
            return $this->where('email', 'like', "%{$value}%");
        });

        factory(Author::class)->create(['name' => 'ZZZSCIENCE-UNIQUE Author', 'email' => 'zzzsci-unique@example.com']);
        factory(Author::class)->create(['name' => 'ZZZHISTORY-UNIQUE Author', 'email' => 'zzzhistory-unique@example.com']);

        $byName = Author::ofType('ZZZSCIENCE-UNIQUE')->get();
        $byEmail = Author::customFilter('zzzhistory-unique')->get();

        $this->assertCount(1, $byName);
        $this->assertCount(1, $byEmail);
        $this->assertNotEquals($byName->first()->id, $byEmail->first()->id);
    }

    /**
     * AC: Global macro results are cached — repeated calls without DB changes
     * return consistent results (proving cache is populated and returned).
     */
    public function testGlobalMacroResultsAreCached(): void
    {
        Builder::macro('type', function (string $type) {
            /** @var Builder $this */
            return $this->where('name', 'like', "%{$type}%");
        });

        factory(Author::class)->create(['name' => 'ZZZCACHED-UNIQUE Author']);
        factory(Author::class)->create(['name' => 'ZZZCACHED-UNIQUE Author 2']);

        // First call populates the cache
        $first = Author::type('ZZZCACHED-UNIQUE')->get();

        // Second call — no DB changes, should return same cached result
        $second = Author::type('ZZZCACHED-UNIQUE')->get();

        $this->assertCount(2, $first);
        $this->assertCount(2, $second, 'Repeated call with same args should return identical cached results.');
        $this->assertEquals(
            $first->pluck('id')->sort()->values()->toArray(),
            $second->pluck('id')->sort()->values()->toArray(),
            'Both calls should return the same records (from cache).'
        );
    }

    /**
     * AC: No regression — existing behavior of standard builder methods
     * (non-macro) continues to work correctly through CachedBuilder.
     */
    public function testExistingBuilderMethodsStillWorkWithCachedBuilder(): void
    {
        factory(Author::class)->create(['name' => 'Alice']);
        factory(Author::class)->create(['name' => 'Bob']);

        $result = Author::where('name', 'Alice')->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Alice', $result->first()->name);
    }

    /**
     * AC: CachedBuilder proxies named local scopes the same way Eloquent does.
     * Regression guard for existing scope behavior.
     */
    public function testLocalScopeStillWorksThroughCachedBuilder(): void
    {
        factory(Author::class)->create(['name' => 'Alpha Author']);
        factory(Author::class)->create(['name' => 'Beta Author']);

        $alphas = Author::startsWithA()->get();
        $uncachedAlphas = (new UncachedAuthor)->startsWithA()->get();

        $this->assertEquals($uncachedAlphas->count(), $alphas->count());
        $this->assertTrue($alphas->every(fn ($a) => str_starts_with($a->name, 'A')));
    }
}
