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

    protected function getCachePrefix() : string
    {
        return "genealabs:laravel-model-caching:"
            . (config('laravel-model-caching.cache-prefix')
                ? config('laravel-model-caching.cache-prefix', '') . ":"
                : "");
    }

    public function make() : array
    {
        $tags = collect($this->eagerLoad)
            ->keys()
            ->map(function ($relationName) {
                $relation = $this->getRelation($relationName);

                return $this->getCachePrefix()
                    . str_slug(get_class($relation->getQuery()->getModel()));
            })
            ->prepend($this->getTagName())
            ->values()
            ->toArray();

        return $tags;
    }

    protected function getRelatedModel($carry) : Model
    {
        if ($carry instanceof Relation) {
            return $carry->getQuery()->getModel();
        }

        return $carry;
    }

    protected function getRelation(string $relationName) : Relation
    {
        return collect(explode('.', $relationName))
            ->reduce(function ($carry, $name) {
                $carry = $carry ?: $this->model;
                $carry = $this->getRelatedModel($carry);

                return $carry->{$name}();
            });
    }

    protected function getTagName() : string
    {
        return $this->getCachePrefix() . str_slug(get_class($this->model));
    }
}
