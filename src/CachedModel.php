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
}
