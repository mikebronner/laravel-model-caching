<?php namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\Traits\CachePrefixing;
use Illuminate\Database\Eloquent\Model;
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

                return $this->getCachePrefix()
                    . (new Str)->slug(get_class($relation->getQuery()->getModel()));
            })
            ->prepend($this->getTagName())
            ->push($this->getTableTagName())
            ->values();

        $joinTags = $this->getJoinTags();

        return $tags->merge($joinTags)
            ->unique()
            ->values()
            ->toArray();
    }

    protected function getJoinTags() : array
    {
        $baseQuery = $this->query;

        if (method_exists($this->query, 'getQuery')) {
            $baseQuery = $this->query->getQuery();
        }

        $joins = $baseQuery->joins ?? [];

        if (empty($joins)) {
            return [];
        }

        $prefix = $this->getCachePrefix();

        return collect($joins)
            ->map(function ($join) {
                $table = $join->table;

                // Strip alias (e.g. "products as p" -> "products")
                if (stripos($table, ' as ') !== false) {
                    $table = trim(explode(' as ', strtolower($table))[0]);
                }

                return $table;
            })
            ->map(function ($table) use ($prefix) {
                return $prefix . (new Str)->slug($table);
            })
            ->unique()
            ->values()
            ->toArray();
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
        return $this->getCachePrefix()
            . (new Str)->slug(get_class($this->model));
    }

    protected function getTableTagName() : string
    {
        return $this->getCachePrefix()
            . (new Str)->slug($this->model->getTable());
    }
}
