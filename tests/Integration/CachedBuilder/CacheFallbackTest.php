<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Log;
use Mockery;

class CacheFallbackTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->cache()->flush();
    }

    public function testCacheReadFailureFallsThroughToDatabaseWhenEnabled(): void
    {
        config(['laravel-model-caching.fallback-to-database' => true]);

        $cacheMock = Mockery::mock(Repository::class);
        $cacheMock->shouldReceive('tags')->andThrow(new \RedisException('Connection refused'));
        $cacheMock->shouldReceive('get')->andThrow(new \RedisException('Connection refused'));
        $cacheMock->shouldReceive('getStore')->andThrow(new \RedisException('Connection refused'));

        $store = Mockery::mock(\Illuminate\Cache\CacheManager::class);
        $store->shouldReceive('store')->andReturn($cacheMock);
        $store->shouldReceive('tags')->andThrow(new \RedisException('Connection refused'));

        app()->singleton('cache', function () use ($store) {
            return $store;
        });

        Log::shouldReceive('warning')
            ->atLeast()
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'laravel-model-caching') && str_contains($message, 'falling back to database');
            });

        // Should not throw, should return results from DB
        $authors = Author::all();

        $this->assertNotNull($authors);
    }

    public function testCacheReadFailureThrowsWhenFallbackDisabled(): void
    {
        config(['laravel-model-caching.fallback-to-database' => false]);

        $cacheMock = Mockery::mock(Repository::class);
        $cacheMock->shouldReceive('tags')->andThrow(new \RedisException('Connection refused'));
        $cacheMock->shouldReceive('get')->andThrow(new \RedisException('Connection refused'));
        $cacheMock->shouldReceive('getStore')->andThrow(new \RedisException('Connection refused'));

        $store = Mockery::mock(\Illuminate\Cache\CacheManager::class);
        $store->shouldReceive('store')->andReturn($cacheMock);
        $store->shouldReceive('tags')->andThrow(new \RedisException('Connection refused'));

        app()->singleton('cache', function () use ($store) {
            return $store;
        });

        $this->expectException(\RedisException::class);

        Author::all();
    }

    public function testCacheFlushFailureLogsWarningWhenFallbackEnabled(): void
    {
        config(['laravel-model-caching.fallback-to-database' => true]);

        // First get a valid author from DB
        $author = (new Author)->newQueryWithoutScopes()
            ->first();

        $this->assertNotNull($author);

        $cacheMock = Mockery::mock(Repository::class);
        $cacheMock->shouldReceive('tags')->andThrow(new \RedisException('Connection refused'));
        $cacheMock->shouldReceive('flush')->andThrow(new \RedisException('Connection refused'));
        $cacheMock->shouldReceive('get')->andThrow(new \RedisException('Connection refused'));
        $cacheMock->shouldReceive('getStore')->andThrow(new \RedisException('Connection refused'));

        $store = Mockery::mock(\Illuminate\Cache\CacheManager::class);
        $store->shouldReceive('store')->andReturn($cacheMock);
        $store->shouldReceive('tags')->andThrow(new \RedisException('Connection refused'));

        app()->singleton('cache', function () use ($store) {
            return $store;
        });

        Log::shouldReceive('warning')
            ->atLeast()
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'laravel-model-caching');
            });

        // flushCache should not throw
        $author->flushCache();
    }

    public function testCacheFlushFailureThrowsWhenFallbackDisabled(): void
    {
        config(['laravel-model-caching.fallback-to-database' => false]);

        $author = (new Author)->newQueryWithoutScopes()
            ->first();

        $this->assertNotNull($author);

        $cacheMock = Mockery::mock(Repository::class);
        $cacheMock->shouldReceive('tags')->andThrow(new \RedisException('Connection refused'));
        $cacheMock->shouldReceive('flush')->andThrow(new \RedisException('Connection refused'));
        $cacheMock->shouldReceive('get')->andThrow(new \RedisException('Connection refused'));
        $cacheMock->shouldReceive('getStore')->andThrow(new \RedisException('Connection refused'));

        $store = Mockery::mock(\Illuminate\Cache\CacheManager::class);
        $store->shouldReceive('store')->andReturn($cacheMock);
        $store->shouldReceive('tags')->andThrow(new \RedisException('Connection refused'));

        app()->singleton('cache', function () use ($store) {
            return $store;
        });

        $this->expectException(\RedisException::class);

        $author->flushCache();
    }

    public function testFallbackConfigDefaultsToFalse(): void
    {
        $this->assertFalse(
            config('laravel-model-caching.fallback-to-database', false)
        );
    }

    public function testMockCacheStoreThrowingVerifiesFallbackEndToEnd(): void
    {
        config(['laravel-model-caching.fallback-to-database' => true]);

        $cacheMock = Mockery::mock(Repository::class);
        $cacheMock->shouldReceive('tags')->andThrow(new \RedisException('Connection refused'));
        $cacheMock->shouldReceive('get')->andThrow(new \RedisException('Connection refused'));
        $cacheMock->shouldReceive('getStore')->andThrow(new \RedisException('Connection refused'));

        $store = Mockery::mock(\Illuminate\Cache\CacheManager::class);
        $store->shouldReceive('store')->andReturn($cacheMock);
        $store->shouldReceive('tags')->andThrow(new \RedisException('Connection refused'));

        app()->singleton('cache', function () use ($store) {
            return $store;
        });

        Log::shouldReceive('warning')
            ->atLeast()
            ->once();

        // get() should fall back to DB
        $authors = Author::query()->get();

        $this->assertNotEmpty($authors);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $authors);
    }
}
