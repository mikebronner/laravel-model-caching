<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use Illuminate\Cache\TaggableStore;

trait Cachable
{
    protected function cache(array $tags = [])
    {
        $cache = cache();

        if (is_subclass_of($cache->getStore(), TaggableStore::class)) {
            $cache = $cache->tags($tags);
        }

        return $cache;
    }
}
