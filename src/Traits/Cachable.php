<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use GeneaLabs\LaravelModelCaching\CacheKey;
use GeneaLabs\LaravelModelCaching\CacheTags;
use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use Illuminate\Database\Eloquent\Model;

use GeneaLabs\LaravelModelCaching\CacheGlobal;

trait Cachable
{
    protected $isCachable = true;

    protected function cache(array $tags = [])
    {
        $cache = cache();

        if (config('laravel-model-caching.store')) {
            $cache = $cache->store(config('laravel-model-caching.store'));
        }

        if (is_subclass_of($cache->getStore(), TaggableStore::class)) {
            if (is_a($this, Model::class)) {
                array_push($tags, $this->makeCachePrefix(str_slug(get_called_class())));
            }

            $cache = $cache->tags($tags);
        }

        return $cache;
    }

    public function disableCache()
    {

        CacheGlobal::disableCache();

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

    protected function retrieveEagerLoad()
    {
        if (is_a($this, Model::class)) {
            return [];
        }
        if (is_a($this, EloquentBuilder::class)) {
            return $this->eagerLoad ?? [];
        }
        return null;
    }

    protected function retrieveCacheModel()
    {
        if (is_a($this, Model::class)) {
            return $this;
        }
        if (is_a($this, EloquentBuilder::class)) {
            return $this->model;
        }
        return null;
    }

    protected function retrieveCacheQuery()
    {
        if (is_a($this, Model::class)) {
            return app(Builder::class);
        }
        if (is_a($this, EloquentBuilder::class)) {
            return $this->query;
        }
        return null;
    }

    protected function makeCachePrefix($elementMix)
    {
        $model = $this->retrieveCacheModel();
        if (!method_exists($model, "getCachePrefix")) {
            return $elementMix;
        }

        $result = null;
        $cachePrefix = $model->getCachePrefix();
        if ($cachePrefix == null) {
            return $elementMix;
        }
        if (is_array($elementMix)) {
            $result = [];
            foreach ($elementMix as $value) {
                array_push($result, $cachePrefix . '-' . $value);
            }
            return $result;
        } else {
            $result = $cachePrefix . '-' . $elementMix;
        }
        return $result;
    }

    protected function makeCacheKey(
        array $columns = ['*'],
        $idColumn = null,
        string $keyDifferentiator = ''
    ) : string {
        $eagerLoad = $this->retrieveEagerLoad();
        $model = $this->retrieveCacheModel();
        $query = $this->retrieveCacheQuery();

        return (new CacheKey($eagerLoad, $model, $query))
            ->make($columns, $idColumn, $this->makeCachePrefix($keyDifferentiator));
    }

    protected function makeCacheTags() : array
    {
        $eagerLoad = $this->retrieveEagerLoad();
        $model = $this->retrieveCacheModel();

        $tags = (new CacheTags($eagerLoad, $model))
            ->make();

        $tags = $this->makeCachePrefix($tags);

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
        if (CacheGlobal::isDisabled()) {
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
        if (CacheGlobal::isDisabled()) {
            CacheGlobal::enableCache();

            return new EloquentBuilder($query);
        }

        return new CachedBuilder($query);
    }
}
