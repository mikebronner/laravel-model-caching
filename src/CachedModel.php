<?php namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\CachedBuilder as Builder;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
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
    use Cachable;

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
