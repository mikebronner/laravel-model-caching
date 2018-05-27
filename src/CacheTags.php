<?php namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Traits\CachePrefixing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;

class CacheTags
{
    use CachePrefixing;

    protected $eagerLoad;
    protected $model;
    protected $query;

    public function __construct(
        array $eagerLoad,
        Model $model,
        Builder $query
    ) {
        $this->eagerLoad = $eagerLoad;
        $this->model = $model;
        $this->query = $query;
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
// dump($tags);
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
