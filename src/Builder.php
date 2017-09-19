<?php namespace GeneaLabs\LaravelCachableModel\Traits;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class Builder extends EloquentBuilder
{
    protected function eagerLoadRelation(array $models, $name, Closure $constraints)
    {
        $relation = $this->getRelation($name);
        $relation->addEagerConstraints($models);
        $constraints($relation);

        $parentName = str_slug(get_class($relation->getParent()));
        $childName = str_slug(get_class($relation->getModel()));
        $results = cache()->tags([$parentName, $childName])
            ->rememberForever("{$parentName}-{$childName}-relation", function () use ($relation) {
                return $relation->getEager();
            });

        return $relation->match(
            $relation->initRelation($models, $name),
            $results,
            $name
        );
    }
}
