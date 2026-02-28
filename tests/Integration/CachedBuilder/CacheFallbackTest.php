<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\ThrowingCacheStore;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Log;

class CacheFallbackTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->cache()->flush();
    }

    private function breakCacheConnection(): void
    {
        $throwingStore = new ThrowingCacheStore();
        $throwingRepo = new Repository($throwingStore);

        app()->singleton('cache', function () use ($throwingRepo) {
            $manager = new class($throwingRepo) {
                private $repo;
                public function __construct($repo) { $this->repo = $repo; }
                public function store($name = null) { return $this->repo; }
                public function driver($driver = null) { return $this->repo; }
                public function __call($method, $args) { return $this->repo->$method(...$args); }
            };
            return $manager;
        });
    }

    public function testCacheReadFailureFallsThroughToDatabaseWhenEnabled(): void
    {
        config(['laravel-model-caching.fallback-to-database' => true]);
        $this->breakCacheConnection();

        Log::shouldReceive('warning')
            ->atLeast()
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'laravel-model-caching');
            });

        $authors = Author::all();

        $this->assertNotNull($authors);
        $this->assertNotEmpty($authors);
    }

    public function testCacheReadFailureThrowsWhenFallbackDisabled(): void
    {
        config(['laravel-model-caching.fallback-to-database' => false]);
        $this->breakCacheConnection();

        $this->expectException(\RedisException::class);

        Author::all();
    }

    public function testCacheFlushFailureLogsWarningWhenFallbackEnabled(): void
    {
        config(['laravel-model-caching.fallback-to-database' => true]);

        $author = (new Author)->newQueryWithoutScopes()->first();
        $this->assertNotNull($author);

        $this->breakCacheConnection();

        Log::shouldReceive('warning')
            ->atLeast()
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'laravel-model-caching');
            });

        $author->flushCache();
    }

    public function testCacheFlushFailureThrowsWhenFallbackDisabled(): void
    {
        config(['laravel-model-caching.fallback-to-database' => false]);

        $author = (new Author)->newQueryWithoutScopes()->first();
        $this->assertNotNull($author);

        $this->breakCacheConnection();

        $this->expectException(\RedisException::class);

        $author->flushCache();
    }

    public function testFallbackConfigDefaultsToFalse(): void
    {
        $this->assertFalse(
            config('laravel-model-caching.fallback-to-database', false)
        );
    }

    public function testMockCacheStoreEndToEndFallback(): void
    {
        config(['laravel-model-caching.fallback-to-database' => true]);
        $this->breakCacheConnection();

        Log::shouldReceive('warning')
            ->atLeast()
            ->once();

        $authors = Author::query()->get();

        $this->assertNotEmpty($authors);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $authors);
    }
}
