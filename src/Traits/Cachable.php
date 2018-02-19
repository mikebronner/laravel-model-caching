<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use GeneaLabs\LaravelModelCaching\CacheKey;
use GeneaLabs\LaravelModelCaching\CacheTags;
use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use GeneaLabs\LaravelModelCaching\CachedBuilder;

trait Cachable
{
    protected $isCachable = true;
    protected static $isCachableKey = 'genealabs:model-caching:is-disabled';

    protected function cache(array $tags = [])
    {
        $cache = cache();

        if (config('laravel-model-caching.store')) {
            $cache = $cache->store(config('laravel-model-caching.store'));
        }

        if (is_subclass_of($cache->getStore(), TaggableStore::class)) {
            $tags = $this->addTagsWhenCalledFromCachedBuilder($tags);
            $cache = $cache->tags($tags);
        }

        return $cache;
    }

    protected function addTagsWhenCalledFromCachedBuilder(array $tags) : array
    {
        $usesCachableTrait = collect(class_uses($this))
            ->contains("GeneaLabs\LaravelModelCaching\Traits\Cachable");

        if (! $usesCachableTrait) {
            array_push($tags, str_slug(get_called_class()));
        }

        return $tags;
    }

    public function disableCache()
    {
        cache()->forever(self::$isCachableKey, true);

        $this->isCachable = false;

        return $this;
    }

    public function flushCache(array $tags = [])
    {
        if (count($tags) === 0) {
            $tags = $this->makeCacheTags();
        }

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
        $tags = (new CacheTags($this->eagerLoad ?? [], $this->model ?? $this))
            ->make();

        return $tags;
    }

    public static function bootCachable()
    {
        static::saved(function ($instance) {
            $instance->flushCache();
        });
    }

    public static function all($columns = ['*'])
    {
        if (cache()->get(self::$isCachableKey)) {
            return parent::all($columns);
        }

        $class = get_called_class();
        $instance = new $class;
        $tags = [str_slug(get_called_class())];
        $key = $instance->makeCacheKey();

        return $instance->cache($tags)
            ->rememberForever($key, function () use ($columns) {
                return parent::all($columns);
            });
    }

    public function newEloquentBuilder($query)
    {
        if (cache()->get(self::$isCachableKey)) {
            cache()->forget(self::$isCachableKey);

            return new EloquentBuilder($query);
        }

        return new CachedBuilder($query);
    }
}
