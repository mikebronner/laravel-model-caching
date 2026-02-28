<?php namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\Traits\Buildable;
use GeneaLabs\LaravelModelCaching\Traits\BuilderCaching;
use GeneaLabs\LaravelModelCaching\Traits\Caching;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Thin builder subclass that intercepts terminal query methods
 * for cache integration. All caching logic is delegated to the
 * Caching trait and CacheKeyGenerator — this class exists only
 * because macros cannot override real methods on Eloquent\Builder.
 *
 * @see \GeneaLabs\LaravelModelCaching\CachingScope  — registers extension methods via global scope
 * @see \GeneaLabs\LaravelModelCaching\CacheKeyGenerator — generates cache keys from a cloned builder
 */
class CachedBuilder extends EloquentBuilder
{
    use Buildable;
    use BuilderCaching;
    use Caching;
}
