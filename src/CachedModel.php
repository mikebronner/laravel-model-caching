<?php namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\Builder;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use LogicException;

use Illuminate\Cache\TaggedCache;

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
        $class = get_called_class();
        $instance = new $class;

        static::created(function () use ($instance) {
            $instance->flushCache();
        });

        static::deleted(function () use ($instance) {
            $instance->flushCache();
        });

        static::saved(function () use ($instance) {
            $instance->flushCache();
        });

        static::updated(function () use ($instance) {
            $instance->flushCache();
        });
    }

    public function cache(array $tags = [])
    {
        $cache = cache();

        if (is_subclass_of(cache()->getStore(), TaggableStore::class)) {
            array_push($tags, str_slug(get_called_class()));
            $cache = cache()->tags($tags);
        }

        return $cache;
    }

    public function flushCache(array $tags = [])
    {
        $this->cache($tags)->flush();
    }
}
