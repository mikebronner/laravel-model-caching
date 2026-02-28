<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Contracts\Cache\Store;

class ThrowingCacheStore implements Store
{
    public function get($key): mixed
    {
        throw new \RedisException('Connection refused');
    }

    public function many(array $keys): array
    {
        throw new \RedisException('Connection refused');
    }

    public function put($key, $value, $seconds): bool
    {
        throw new \RedisException('Connection refused');
    }

    public function putMany(array $values, $seconds): bool
    {
        throw new \RedisException('Connection refused');
    }

    public function increment($key, $value = 1): int|bool
    {
        throw new \RedisException('Connection refused');
    }

    public function decrement($key, $value = 1): int|bool
    {
        throw new \RedisException('Connection refused');
    }

    public function forever($key, $value): bool
    {
        throw new \RedisException('Connection refused');
    }

    public function forget($key): bool
    {
        throw new \RedisException('Connection refused');
    }

    public function flush(): bool
    {
        throw new \RedisException('Connection refused');
    }

    public function getPrefix(): string
    {
        return '';
    }
}
