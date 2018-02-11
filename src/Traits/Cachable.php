<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use GeneaLabs\LaravelModelCaching\CacheKey;
use GeneaLabs\LaravelModelCaching\CacheTags;
use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Query\Builder;

trait Cachable
{
    protected $isCachable = true;

    protected function cache(array $tags = [])
    {
        $cache = cache();

        if (config('laravel-model-caching.store')) {
            $cache = $cache->store(config('laravel-model-caching.store'));
        }

        if (is_subclass_of($cache->getStore(), TaggableStore::class)) {
            if (is_a($this, CachedModel::class)) {
                array_push($tags, str_slug(get_called_class()));
            }

            $cache = $cache->tags($tags);
        }

        return $cache;
    }

    public function disableCache()
    {
        session(['genealabs-laravel-model-caching-is-disabled' => true]);
        $this->isCachable = false;

        return $this;
    }

    public function flushCache(array $tags = [])
    {
        $this->cache($tags)->flush();
    }

    protected function makeCacheKey(
        array $columns = ['*'],
        $idColumn = null,
        string $keyDifferentiator = ''
    ) : string {
        $eagerLoad = $this->eagerLoad ?? [];
        $model = $this->model ?? $this;
        $query = $this->query ?? app(Builder::class);

        return (new CacheKey($eagerLoad, $model, $query))
            ->make($columns, $idColumn, $keyDifferentiator);
    }

    protected function makeCacheTags() : array
    {
        return (new CacheTags($this->eagerLoad, $this->model))
            ->make();
    }

    public static function bootCachable()
    {
        static::saved(function ($instance) {
            $instance->flushCache();
        });
    }

    public static function all($columns = ['*'])
    {
        $class = get_called_class();
        $instance = new $class;
        $tags = [str_slug(get_called_class())];
        $key = $instance->makeCacheKey();

        return $instance->cache($tags)
            ->rememberForever($key, function () use ($columns) {
                return parent::all($columns);
            });
    }
}
