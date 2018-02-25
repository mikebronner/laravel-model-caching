<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\CachedModel;
use GeneaLabs\LaravelModelCaching\CacheKey;
use GeneaLabs\LaravelModelCaching\CacheTags;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;

trait Cachable
{
    protected $isCachable = true;

    public function cache(array $tags = [])
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
        $this->isCachable = false;

        return $this;
    }

    public function flushCache(array $tags = [])
    {
        if (count($tags) === 0) {
            $tags = $this->makeCacheTags();
        }

        $this->cache($tags)->flush();

        [$cacheCooldown, $invalidatedAt, $savedAt] = $this->getModelCacheCooldown($this);

        if ($cacheCooldown) {
            $cachePrefix = "genealabs:laravel-model-caching:"
                . (config('laravel-model-caching.cache-prefix')
                    ? config('laravel-model-caching.cache-prefix', '') . ":"
                    : "");
            $modelClassName = get_class($this);
            $cacheKey = "{$cachePrefix}:{$modelClassName}-cooldown:saved-at";

            $this->cache()
                ->rememberForever($cacheKey, function () {
                    return now();
                });
        }
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

    protected function getModelCacheCooldown(Model $instance)
    {
        $cachePrefix = "genealabs:laravel-model-caching:"
            . (config('laravel-model-caching.cache-prefix')
                ? config('laravel-model-caching.cache-prefix', '') . ":"
                : "");
        $modelClassName = get_class($instance);

        $cacheCooldown = $instance
            ->cache()
            ->get("{$cachePrefix}:{$modelClassName}-cooldown:seconds");

        if (! $cacheCooldown) {
            return [null, null, null];
        }

        $invalidatedAt = $instance
            ->cache()
            ->get("{$cachePrefix}:{$modelClassName}-cooldown:invalidated-at");

        $savedAt = $instance
            ->cache()
            ->get("{$cachePrefix}:{$modelClassName}-cooldown:saved-at");

        return [
            $cacheCooldown,
            $invalidatedAt,
            $savedAt,
        ];
    }

    protected function checkCooldownAndRemoveIfExpired(Model $instance)
    {
        [$cacheCooldown, $invalidatedAt] = $this->getModelCacheCooldown($instance);

        if (! $cacheCooldown) {
            return;
        }

        if (now()->diffInSeconds($invalidatedAt) >= $cacheCooldown) {
            $cachePrefix = "genealabs:laravel-model-caching:"
                . (config('laravel-model-caching.cache-prefix')
                    ? config('laravel-model-caching.cache-prefix', '') . ":"
                    : "");
            $modelClassName = get_class($instance);

            $instance
                ->cache()
                ->forget("{$cachePrefix}:{$modelClassName}-cooldown:invalidated-at");
            $instance
                ->cache()
                ->forget("{$cachePrefix}:{$modelClassName}-cooldown:invalidated-at");
            $instance
                ->cache()
                ->forget("{$cachePrefix}:{$modelClassName}-cooldown:saved-at");
            $instance->flushCache();
        }
    }

    protected function checkCooldownAndFlushAfterPersiting(Model $instance)
    {
        [$cacheCooldown, $invalidatedAt, $savedAt] = $instance->getModelCacheCooldown($instance);

        if (! $cacheCooldown) {
            $instance->flushCache();

            return;
        }

        if ($cacheCooldown) {
            $cachePrefix = "genealabs:laravel-model-caching:"
                . (config('laravel-model-caching.cache-prefix')
                    ? config('laravel-model-caching.cache-prefix', '') . ":"
                    : "");
            $modelClassName = get_class($instance);
            $cacheKey = "{$cachePrefix}:{$modelClassName}-cooldown:saved-at";

            $instance->cache()
                ->rememberForever($cacheKey, function () {
                    return now();
                });
        }

        if ($savedAt > $invalidatedAt
                && now()->diffInSeconds($invalidatedAt) >= $cacheCooldown
        ) {
            $instance->flushCache();
        }
    }

    public static function bootCachable()
    {
        // TODO: add for deleted,updated,etc?
        static::saved(function ($instance) {
            $instance->checkCooldownAndFlushAfterPersiting($instance);
        });
    }

    public static function all($columns = ['*'])
    {
        if (config('laravel-model-caching.disabled')) {
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
        if (! $this->isCachable()) {
            $this->isCachable = true;

            return new EloquentBuilder($query);
        }

        return new CachedBuilder($query);
    }

    public function isCachable() : bool
    {
        return $this->isCachable
            && ! config('laravel-model-caching.disabled');
    }

    public function scopeWithCacheCooldownSeconds(
        EloquentBuilder $query,
        int $seconds
    ) : EloquentBuilder {
        $cachePrefix = "genealabs:laravel-model-caching:"
            . (config('laravel-model-caching.cache-prefix')
                ? config('laravel-model-caching.cache-prefix', '') . ":"
                : "");
        $modelClassName = get_class($this);
        $cacheKey = "{$cachePrefix}:{$modelClassName}-cooldown:seconds";

        $this->cache()
            ->rememberForever($cacheKey, function () use ($seconds) {
                return $seconds;
            });

        $cacheKey = "{$cachePrefix}:{$modelClassName}-cooldown:invalidated-at";
        $this->cache()
            ->rememberForever($cacheKey, function () use ($seconds) {
                return now();
            });

        return $query;
    }
}
