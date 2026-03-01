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
            ->flatMap(function ($relationName) {
                $morphToTags = $this->getMorphToTagsForRelation($relationName);

                if ($morphToTags !== null) {
                    return $morphToTags;
                }

                $relation = $this->getRelation($relationName);

                if (! $relation) {
                    return [];
                }

                return [$this->getCachePrefix()
                    . (new Str)->slug(get_class($relation->getQuery()->getModel()))];
            })
            ->filter()
            ->unique()
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

    protected function getMorphToTagsForRelation(string $relationName) : ?array
    {
        $segments = explode('.', $relationName);
        $model = $this->model;

        foreach ($segments as $segment) {
            if (! method_exists($model, $segment)) {
                return null;
            }

            $relation = $model->{$segment}();

            if ($relation instanceof MorphTo) {
                return $this->getMorphToTags($relation);
            }

            if (! ($relation instanceof Relation)) {
                return null;
            }

            $model = $relation->getQuery()->getModel();
        }

        return null;
    }

    protected function getMorphToTags(MorphTo $relation) : array
    {
        $morphMap = Relation::morphMap();

        if (! empty($morphMap)) {
            $tags = [];

            foreach ($morphMap as $type) {
                if (class_exists($type)) {
                    $tags[] = $this->getCachePrefix() . (new Str)->slug($type);
                }
            }

            if (! empty($tags)) {
                return $tags;
            }
        }

        $morphType = $relation->getMorphType();
        $column = last(explode('.', $morphType));
        $table = $relation->getParent()->getTable();

        $types = $relation->getParent()
            ->newQuery()
            ->getQuery()
            ->select($column)
            ->from($table)
            ->whereNotNull($column)
            ->distinct()
            ->pluck($column)
            ->toArray();

        $tags = [];

        foreach ($types as $type) {
            $resolved = Relation::getMorphedModel($type) ?? $type;

            if (class_exists($resolved)) {
                $tags[] = $this->getCachePrefix() . (new Str)->slug($resolved);
            }
        }

        return $tags;
    }

    protected function getTagName() : string
    {
        return $this->getCachePrefix()
            . (new Str)->slug(get_class($this->model));
    }
}
