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
            . (config('genealabs:laravel-model-caching', '')
                ? config('genealabs:laravel-model-caching', '') . ":"
                : "");
    }

    public function make() : array
    {
        $tags = collect($this->eagerLoad)
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

                return $this->getCachePrefix()
                    . str_slug(get_class($relation->getQuery()->getModel()));
            })
            ->prepend(
                $this->getCachePrefix()
                    . str_slug(get_class($this->model))
            )
            ->values()
            ->toArray();

        return $tags;
    }
}
