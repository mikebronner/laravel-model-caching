<?php namespace GeneaLabs\LaravelCachableModel\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\TaggableStore;

abstract class CachedModel extends Model
{
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
}
