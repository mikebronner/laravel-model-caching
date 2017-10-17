<?php namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\CachedBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class CacheTags
{
    protected $eagerLoad;
    protected $model;

    public function __construct(array $eagerLoad, Model $model)
    {
        $this->eagerLoad = $eagerLoad;
        $this->model = $model;
    }

    public function make() : array
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
                            $carry = $carry->getQuery()->getModel();
                        }

                        return $carry->{$name}();
                    });

                return str_slug(get_class($relation->getQuery()->getModel()));
            })
            ->prepend(str_slug(get_class($this->model)))
            ->values()
            ->toArray();
    }
}
