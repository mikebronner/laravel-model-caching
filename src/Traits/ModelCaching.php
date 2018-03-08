<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use GeneaLabs\LaravelModelCaching\CachedBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait ModelCaching
{
    public static function all($columns = ['*'])
    {
        $class = get_called_class();
        $instance = new $class;

        if (config('laravel-model-caching.disabled') || !$instance->isCachable) {
            return parent::all($columns);
        }

        $tags = [str_slug(get_called_class())];
        $key = $instance->makeCacheKey();

        return $instance->cache($tags)
            ->rememberForever($key, function () use ($columns) {
                return parent::all($columns);
            });
    }

    public static function bootCachable()
    {
        static::saved(function ($instance) {
            $instance->checkCooldownAndFlushAfterPersiting($instance);
        });

        static::pivotAttached(function ($instance) {
            $instance->checkCooldownAndFlushAfterPersiting($instance);
        });

        static::pivotDetached(function ($instance) {
            $instance->checkCooldownAndFlushAfterPersiting($instance);
        });

        static::pivotUpdated(function ($instance) {
            $instance->checkCooldownAndFlushAfterPersiting($instance);
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

    public function scopeDisableCache(EloquentBuilder $query) : EloquentBuilder
    {
        if ($this->isCachable()) {
            $query = $query->disableModelCaching();
        }

        return $query;
    }

    public function scopeWithCacheCooldownSeconds(
        EloquentBuilder $query,
        int $seconds
    ) : EloquentBuilder {
        $cachePrefix = $this->getCachePrefix();
        $modelClassName = get_class($this);
        $cacheKey = "{$cachePrefix}:{$modelClassName}-cooldown:seconds";

        $this->cache()
            ->rememberForever($cacheKey, function () use ($seconds) {
                return $seconds;
            });

        $cacheKey = "{$cachePrefix}:{$modelClassName}-cooldown:invalidated-at";
        $this->cache()
            ->rememberForever($cacheKey, function () {
                return now();
            });

        return $query;
    }
}
