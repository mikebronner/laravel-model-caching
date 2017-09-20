<?php namespace GeneaLabs\LaravelModelCaching;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use LogicException;

abstract class CachedModel extends Model
{
    protected function getRelationshipFromMethod($method)
    {
        $relation = $this->$method();

        if (! $relation instanceof Relation) {
            throw new LogicException(get_class($this).'::'.$method.' must return a relationship instance.');
        }

        $results = $this->cache([$method])
            ->rememberForever(str_slug(get_called_class()) . "-{$method}", function () use ($relation) {
                return $relation->getResults();
            });

        return tap($results, function ($results) use ($method) {
            $this->setRelation($method, $results);
        });
    }

    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    public static function boot()
    {
        parent::boot();

        static::created(function () {
            self::flushCache();
        });

        static::deleted(function () {
            self::flushCache();
        });

        static::saved(function () {
            self::flushCache();
        });

        static::updated(function () {
            self::flushCache();
        });
    }

    public function cache(array $tags = [])
    {
        $cache = cache();

        if (is_subclass_of(cache()->getStore(), TaggableStore::class)) {
            array_push($tags, str_slug(get_called_class()));
            $cache = $cache->tags($tags);
        }

        return $cache;
    }

    public static function flushCache()
    {
        cache()->tags([str_slug(get_called_class())])
            ->flush();
    }
}
