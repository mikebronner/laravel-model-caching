<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use GeneaLabs\LaravelModelCaching\CachedBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;

trait CacheTagable
{
    public function makeCacheTags(CachedBuilder $builder) : array
    {
        return collect($builder->eagerLoad)
            ->keys()
            ->map(function ($relationName) use ($builder) {
                $relation = collect(explode('.', $relationName))
                    ->reduce(function ($carry, $name) use ($builder) {
                        if (! $carry) {
                            $carry = $builder->model;
                        }

                        if ($carry instanceof Relation) {
                            $carry = $carry->getQuery()->model;
                        }

                        return $carry->{$name}();
                    });

                return str_slug(get_class($relation->getQuery()->model));
            })
            ->prepend(str_slug(get_class($builder->model)))
            ->values()
            ->toArray();
    }
}
