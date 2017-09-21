<?php namespace GeneaLabs\LaravelModelCaching;

use Closure;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class Builder extends EloquentBuilder
{
    protected function eagerLoadRelation(array $models, $name, Closure $constraints)
    {
        $relation = $this->getRelation($name);
        $relation->addEagerConstraints($models);
        $constraints($relation);

        $parentIds = implode('_', collect($models)->pluck('id')->toArray());
        $parentName = str_slug(get_class($relation->getParent()));
        $childName = str_slug(get_class($relation->getRelated()));
        $cache = cache();

        if (is_subclass_of(cache()->getStore(), TaggableStore::class)) {
            $cache->tags([$parentName, $childName]);
        }

        $results = $cache
            ->rememberForever("{$parentName}_{$parentIds}-{$childName}s", function () use ($relation) {
                return $relation->getEager();
            });

        return $relation->match(
            $relation->initRelation($models, $name),
            $results,
            $name
        );
    }
}
