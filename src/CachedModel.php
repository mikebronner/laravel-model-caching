<?php namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\CachedBuilder as Builder;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\TaggableStore;
use Illuminate\Cache\TaggedCache;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use LogicException;

abstract class CachedModel extends Model
{
    public function newEloquentBuilder($query)
    {
        if (session('genealabs-laravel-model-caching-is-disabled')) {
            session()->forget('genealabs-laravel-model-caching-is-disabled');

            return new EloquentBuilder($query);
        }

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

        if (is_subclass_of($cache->getStore(), TaggableStore::class)) {
            array_push($tags, str_slug(get_called_class()));
            $cache = $cache->tags($tags);
        }

        return $cache;
    }

    public function disableCache() : self
    {
        session(['genealabs-laravel-model-caching-is-disabled' => true]);

        return $this;
    }

    public function flushCache(array $tags = [])
    {
        $this->cache($tags)->flush();
    }

    public static function all($columns = ['*'])
    {
        $class = get_called_class();
        $instance = new $class;
        $tags = [str_slug(get_called_class())];
        $key = 'genealabslaravelmodelcachingtestsfixturesauthor';

        return $instance->cache($tags)
            ->rememberForever($key, function () use ($columns) {
                return parent::all($columns);
            });
    }
}
