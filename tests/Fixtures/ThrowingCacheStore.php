<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Contracts\Cache\Store;

class ThrowingCacheStore implements Store
{
    private string $exceptionClass;

    private string $message;

    public function __construct(string $exceptionClass = \RedisException::class, string $message = 'Connection refused')
    {
        $this->exceptionClass = $exceptionClass;
        $this->message = $message;
    }

    private function throwException(): never
    {
        throw new ($this->exceptionClass)($this->message);
    }

    public function get($key): mixed
    {
        $this->throwException();
    }

    public function many(array $keys): array
    {
        $this->throwException();
    }

    public function put($key, $value, $seconds): bool
    {
        $this->throwException();
    }

    public function putMany(array $values, $seconds): bool
    {
        $this->throwException();
    }

    public function increment($key, $value = 1): int|bool
    {
        $this->throwException();
    }

    public function decrement($key, $value = 1): int|bool
    {
        $this->throwException();
    }

    public function forever($key, $value): bool
    {
        $this->throwException();
    }

    public function forget($key): bool
    {
        $this->throwException();
    }

    public function flush(): bool
    {
        $this->throwException();
    }

    public function getPrefix(): string
    {
        return '';
    }
}
