<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use GeneaLabs\LaravelModelCaching\CachedBelongsToMany;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\CachedHasManyThrough;
use GeneaLabs\LaravelModelCaching\CachedHasOneThrough;
use GeneaLabs\LaravelModelCaching\EloquentBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Container\Container;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

trait ModelCaching
{
    public function __get($key)
    {
        if ($key === "cachePrefix") {
            return $this->cachePrefix
                ?? "";
        }

        if ($key === "cacheCooldownSeconds") {
            return $this->cacheCooldownSeconds
                ?? 0;
        }

        if ($key === "query") {
            return $this->query
                ?? $this->newModelQuery();
        }

        return parent::__get($key);
    }

    public function __set($key, $value)
    {
        if ($key === "cachePrefix") {
            $this->cachePrefix = $value;
        }

        if ($key === "cacheCooldownSeconds") {
            $this->cacheCooldownSeconds = $value;
        }

        parent::__set($key, $value);
    }

    public static function all($columns = ['*'])
    {
        $class = get_called_class();
        $instance = new $class;

	    if (!$instance->isCachable()) {
		    return parent::all($columns);
	    }

        try {
            $tags = $instance->makeCacheTags();
            $key = $instance->makeCacheKey();

            return $instance->cache($tags)
                ->rememberForever($key, function () use ($columns) {
                    return parent::all($columns);
                });
        } catch (\Exception $exception) {
            $shouldFallback = Container::getInstance()
                ->make("config")
                ->get("laravel-model-caching.fallback-to-database", false);

            if (! $shouldFallback) {
                throw $exception;
            }

            Log::warning("laravel-model-caching: cache read failed, falling back to database — {$exception->getMessage()}");

            return parent::all($columns);
        }
    }

    public static function bootCachable()
    {
        static::created(function ($instance) {
            $instance->checkCooldownAndFlushAfterPersisting($instance);
        });

        static::deleted(function ($instance) {
            $instance->checkCooldownAndFlushAfterPersisting($instance);
        });

        static::saved(function ($instance) {
            $instance->checkCooldownAndFlushAfterPersisting($instance);
        });

        // TODO: figure out how to add this listener
        // static::restored(function ($instance) {
        //     $instance->checkCooldownAndFlushAfterPersisting($instance);
        // });

        static::pivotSynced(function ($instance, $relationship) {
            $instance->checkCooldownAndFlushAfterPersisting($instance, $relationship);
        });

        static::pivotAttached(function ($instance, $relationship) {
            $instance->checkCooldownAndFlushAfterPersisting($instance, $relationship);
        });

        static::pivotDetached(function ($instance, $relationship) {
            $instance->checkCooldownAndFlushAfterPersisting($instance, $relationship);
        });

        static::pivotUpdated(function ($instance, $relationship) {
            $instance->checkCooldownAndFlushAfterPersisting($instance, $relationship);
        });
    }

    public static function destroy($ids)
    {
        $class = get_called_class();
        $instance = new $class;

        try {
            $instance->flushCache();
        } catch (\Exception $exception) {
            $shouldFallback = Container::getInstance()
                ->make("config")
                ->get("laravel-model-caching.fallback-to-database", false);

            if (! $shouldFallback) {
                throw $exception;
            }

            Log::warning("laravel-model-caching: cache flush failed during destroy — {$exception->getMessage()}");
        }

        return parent::destroy($ids);
    }

    public function newEloquentBuilder($query)
    {
        if (! $this->isCachable()) {
            $this->isCachable = false;

            return new EloquentBuilder($query);
        }

        return new CachedBuilder($query);
    }

    protected function isThroughRelationCachable(Builder $query, Model $farParent): bool
    {
        $relatedIsCachable = method_exists($query->getModel(), 'isCachable')
            && $query->getModel()->isCachable();
        $parentIsCachable = method_exists($farParent, 'isCachable')
            && $farParent->isCachable();

        return $relatedIsCachable || $parentIsCachable;
    }

    protected function newHasManyThrough(
        Builder $query,
        Model $farParent,
        Model $throughParent,
        $firstKey,
        $secondKey,
        $localKey,
        $secondLocalKey
    ) {
        if ($this->isThroughRelationCachable($query, $farParent)) {
            return new CachedHasManyThrough(
                $query,
                $farParent,
                $throughParent,
                $firstKey,
                $secondKey,
                $localKey,
                $secondLocalKey
            );
        }

        return parent::newHasManyThrough(
            $query,
            $farParent,
            $throughParent,
            $firstKey,
            $secondKey,
            $localKey,
            $secondLocalKey
        );
    }

    protected function newHasOneThrough(
        Builder $query,
        Model $farParent,
        Model $throughParent,
        $firstKey,
        $secondKey,
        $localKey,
        $secondLocalKey
    ) {
        if ($this->isThroughRelationCachable($query, $farParent)) {
            return new CachedHasOneThrough(
                $query,
                $farParent,
                $throughParent,
                $firstKey,
                $secondKey,
                $localKey,
                $secondLocalKey
            );
        }

        return parent::newHasOneThrough(
            $query,
            $farParent,
            $throughParent,
            $firstKey,
            $secondKey,
            $localKey,
            $secondLocalKey
        );
    }

    protected function newBelongsToMany(
        Builder $query,
        Model $parent,
        $table,
        $foreignPivotKey,
        $relatedPivotKey,
        $parentKey,
        $relatedKey,
        $relationName = null
    ) {
        $relatedIsCachable = method_exists($query->getModel(), "isCachable")
            && $query->getModel()->isCachable();
        $parentIsCachable = method_exists($parent, "isCachable")
            && $parent->isCachable();

        if ($relatedIsCachable || $parentIsCachable) {
            return new CachedBelongsToMany(
                $query,
                $parent,
                $table,
                $foreignPivotKey,
                $relatedPivotKey,
                $parentKey,
                $relatedKey,
                $relationName
            );
        }

        return new BelongsToMany(
            $query,
            $parent,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relationName
        );
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
        ?int $seconds = null
    ) : EloquentBuilder {
        if (! $seconds) {
            $seconds = $this->cacheCooldownSeconds;
        }

        try {
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
        } catch (\Exception $exception) {
            $shouldFallback = Container::getInstance()
                ->make("config")
                ->get("laravel-model-caching.fallback-to-database", false);

            if (! $shouldFallback) {
                throw $exception;
            }

            Log::warning("laravel-model-caching: cache cooldown write failed — {$exception->getMessage()}");
        }

        return $query;
    }
}
