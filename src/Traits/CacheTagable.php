<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;

trait CacheTagable
{
    public function makeCacheTags() : array
    {
        return collect($this->eagerLoad)
            ->keys()
            ->map(function ($relationName) {
                $relation = collect(explode('.', $relationName))
                    ->reduce(function ($carry, $name) {
                        if (! $carry) {
                            $carry = $this->model;
                        }

                        if ($carry instanceof Relation) {
                            $carry = $carry->getQuery()->model;
                        }

                        return $carry->{$name}();
                    });

                return str_slug(get_class($relation->getQuery()->model));
            })
            ->prepend(str_slug(get_class($this->model)))
            ->values()
            ->toArray();
    }
}
