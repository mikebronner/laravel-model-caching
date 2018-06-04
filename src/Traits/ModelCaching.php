<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use Carbon\Carbon;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait ModelCaching
{
    public static function all($columns = ['*'])
    {
        if (config('laravel-model-caching.disabled')) {
            return parent::all($columns);
        }

        $class = get_called_class();
        $instance = new $class;
        $tags = $instance->makeCacheTags();
        $key = $instance->makeCacheKey();

        return $instance->cache($tags)
            ->rememberForever($key, function () use ($columns) {
                return parent::all($columns);
            });
    }

    public static function bootCachable()
    {
        static::created(function ($instance) {
            $instance->checkCooldownAndFlushAfterPersiting($instance);
        });

        static::deleted(function ($instance) {
            $instance->checkCooldownAndFlushAfterPersiting($instance);
        });

        static::saved(function ($instance) {
            $instance->checkCooldownAndFlushAfterPersiting($instance);
        });

        // TODO: figure out how to add this listener
        // static::restored(function ($instance) {
        //     $instance->checkCooldownAndFlushAfterPersiting($instance);
        // });

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

    public static function destroy($ids)
    {
        $class = get_called_class();
        $instance = new $class;
        $instance->flushCache();

        return parent::destroy($ids);
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
                return (new Carbon)->now();
            });

        return $query;
    }
}
