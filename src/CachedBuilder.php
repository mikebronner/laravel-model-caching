<?php namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CachedBuilder extends EloquentBuilder
{
    use Cachable;

    public function avg($column)
    {
        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey(['*'], null, "-avg_{$column}");
        $method = 'avg';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function count($columns = ['*'])
    {
        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey(['*'], null, "-count");
        $method = 'count';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function cursor()
    {
        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey(['*'], null, "-cursor");
        $method = 'cursor';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function delete()
    {
        $this->cache($this->makeCacheTags())
            ->flush();

        return parent::delete();
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function find($id, $columns = ['*'])
    {
        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey($columns);
        $method = 'find';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function first($columns = ['*'])
    {
        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey($columns);
        $method = 'first';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function get($columns = ['*'])
    {
        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey($columns);
        $method = 'get';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function max($column)
    {
        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey(['*'], null, "-max_{$column}");
        $method = 'max';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function min($column)
    {
        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey(['*'], null, "-min_{$column}");
        $method = 'min';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function pluck($column, $key = null)
    {
        $keyDifferentiator = "-pluck_{$column}" . ($key ? "_{$key}" : "");
        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey([$column], null, $keyDifferentiator);
        $method = 'pluck';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function sum($column)
    {
        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey(['*'], null, "-sum_{$column}");
        $method = 'sum';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function value($column)
    {
        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey(['*'], null, "-value_{$column}");
        $method = 'value';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function cachedValue(array $arguments, string $cacheKey, string $method)
    {
        $cacheTags = $this->makeCacheTags();
        $hashedCacheKey = sha1($cacheKey);

        $result = $this->retrieveCachedValue(
            $arguments,
            $cacheKey,
            $cacheTags,
            $hashedCacheKey,
            $method
        );

        return $this->preventHashCollision(
            $result,
            $arguments,
            $cacheKey,
            $cacheTags,
            $hashedCacheKey,
            $method
        );
    }

    protected function preventHashCollision(
        array $result,
        array $arguments,
        string $cacheKey,
        array $cacheTags,
        string $hashedCacheKey,
        string $method
    ) {
        if ($result['key'] !== $cacheKey) {
            cache()->tags($cacheTags)->forget($hashedCacheKey);

            $result = $this->retrieveCachedValue(
                $arguments,
                $cacheKey,
                $cacheTags,
                $hashedCacheKey,
                $method
            );
        }

        return $result['value'];
    }

    protected function retrieveCachedValue(
        array $arguments,
        string $cacheKey,
        array $cacheTags,
        string $hashedCacheKey,
        string $method
    ) {
        return $this->cache($cacheTags)
            ->rememberForever(
                $hashedCacheKey,
                function () use ($arguments, $cacheKey, $method) {
                    return [
                        'key' => $cacheKey,
                        'value' => parent::{$method}(...$arguments),
                    ];
                }
            );
    }
}
