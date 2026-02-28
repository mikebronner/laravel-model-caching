<?php namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\Traits\CachePrefixing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

class CacheTags
{
    use CachePrefixing;

    protected $eagerLoad;
    protected $model;
    protected $query;

    public function __construct(
        array $eagerLoad,
        $model,
        $query
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

                if (! $relation) {
                    return null;
                }

                return $this->getCachePrefix()
                    . (new Str)->slug(get_class($relation->getQuery()->getModel()));
            })
            ->filter()
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

    protected function getRelation(string $relationName) : ?Relation
    {
        return collect(explode('.', $relationName))
            ->reduce(function ($carry, $name) {
                $carry = $carry ?: $this->model;
                $carry = $this->getRelatedModel($carry);

                if (! method_exists($carry, $name)) {
                    return null;
                }

                $relation = $carry->{$name}();

                // MorphTo cannot be resolved to a concrete type statically;
                // the actual model class depends on the row's morph-type
                // column. Stop the chain here so downstream segments
                // (e.g. "commentable.tags") don't blow up calling a method
                // that only exists on one of the possible morph targets.
                if ($relation instanceof MorphTo) {
                    return null;
                }

                return $relation;
            });
    }

    protected function getTagName() : string
    {
        return $this->getCachePrefix()
            . (new Str)->slug(get_class($this->model));
    }
}
