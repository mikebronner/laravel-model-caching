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
        if (! $this->isCachable()) {
            return parent::avg($column);
        }

        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey(['*'], null, "-avg_{$column}");
        $method = 'avg';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function count($columns = ['*'])
    {
        if (! $this->isCachable()) {
            return parent::count($columns);
        }

        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey(['*'], null, "-count");
        $method = 'count';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function cursor()
    {
        if (! $this->isCachable()) {
            return collect(parent::cursor());
        }

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
        if (! $this->isCachable()) {
            return parent::find($id, $columns);
        }

        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey(['*'], null, "-find_{$id}");
        $method = 'find';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function first($columns = ['*'])
    {
        if (! $this->isCachable()) {
            return parent::first($columns);
        }

        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey($columns);
        $method = 'first';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function get($columns = ['*'])
    {
        if (! $this->isCachable()) {
            return parent::get($columns);
        }

        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey($columns);
        $method = 'get';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function max($column)
    {
        if (! $this->isCachable()) {
            return parent::max($column);
        }

        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey(['*'], null, "-max_{$column}");
        $method = 'max';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function min($column)
    {
        if (! $this->isCachable()) {
            return parent::min($column);
        }

        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey(['*'], null, "-min_{$column}");
        $method = 'min';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function paginate(
        $perPage = null,
        $columns = ['*'],
        $pageName = 'page',
        $page = null
    ) {
        if (! $this->isCachable()) {
            return parent::paginate($perPage, $columns, $pageName, $page);
        }

        $arguments = func_get_args();
        $page = $page ?: 1;
        $cacheKey = $this->makeCacheKey($columns, null, "-paginate_by_{$perPage}_{$pageName}_{$page}");
        $method = 'paginate';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function pluck($column, $key = null)
    {
        if (! $this->isCachable()) {
            return parent::pluck($column, $key);
        }

        $keyDifferentiator = "-pluck_{$column}" . ($key ? "_{$key}" : "");
        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey([$column], null, $keyDifferentiator);
        $method = 'pluck';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function sum($column)
    {
        if (! $this->isCachable()) {
            return parent::sum($column);
        }

        $arguments = func_get_args();
        $cacheKey = $this->makeCacheKey(['*'], null, "-sum_{$column}");
        $method = 'sum';

        return $this->cachedValue($arguments, $cacheKey, $method);
    }

    public function value($column)
    {
        if (! $this->isCachable()) {
            return parent::value($column);
        }

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
            $this->cache()
                ->tags($cacheTags)
                ->forget($hashedCacheKey);

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
