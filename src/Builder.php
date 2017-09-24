<?php namespace GeneaLabs\LaravelModelCaching;

use Closure;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

class Builder extends EloquentBuilder
{
    protected function cache(array $tags = [])
    {
        $cache = cache();

        if (is_subclass_of($cache->getStore(), TaggableStore::class)) {
            $cache = $cache->tags($tags);
        }

        return $cache;
    }

    // protected function cacheResults(Relation $relation, array $models, string $name) : array
    // {
    //     $parentIds = implode('_', collect($models)->pluck('id')->toArray());
    //     $parentName = str_slug(get_class($relation->getParent()));
    //     $childName = str_slug(get_class($relation->getRelated()));
    //
    //     $cachedResults = $this->cache([$parentName, $childName])->rememberForever(
    //         "{$parentName}_{$parentIds}-{$childName}s",
    //         function () use ($relation, $models, $name) {
    //             return $relation->match(
    //                $relation->initRelation($models, $name),
    //                $relation->getEager(),
    //                $name
    //            );
    //         }
    //     );
    //
    //     return $cachedResults;
    // }
    //
    // protected function eagerLoadRelation(array $models, $name, Closure $constraints)
    // {
    //     $relation = $this->getRelation($name);
    //     $relation->addEagerConstraints($models);
    //     $constraints($relation);
    //
    //     return $this->cacheResults($relation, $models, $name);
    // }

    protected function getCacheKey(array $columns = ['*'], $ids = null) : string
    {
        $key = str_slug(get_class($this->model));

        if ($ids) {
            $key .= '_' . (is_array($ids)
                ? implode('_', $ids)
                : $ids);
        }

        if ($columns !== ['*']) {
            $key .= '_' . implode('_', $columns);
        }

        $key .= collect($this->query->wheres)->reduce(function ($carry, $where) {
            $value = $where['value'] ?? implode('_', $where['values']) ?? '';

            return "{$carry}-{$where['column']}_{$value}";
        });

        if (collect($this->eagerLoad)->isNotEmpty()) {
            $key .= '-' . implode('-', collect($this->eagerLoad)->keys()->toArray());
        }

        if ($this->query->offset) {
            $key .= "-offset_{$this->query->offset}";
        }

        if ($this->query->limit) {
            $key .= "-limit_{$this->query->limit}";
        }

        return $key;
    }

    protected function getCacheTags() : array
    {
        return collect($this->eagerLoad)->keys()
            ->map(function ($name) {
                return str_slug(get_class(
                    $this->model
                        ->{$name}()
                        ->getQuery()
                        ->model
                ));
            })
            ->prepend(str_slug(get_class($this->model)))
            ->values()
            ->toArray();
    }

    public function avg($column)
    {
        $tags = [str_slug(get_class($this->model))];
        $key = str_slug(get_class($this->model)) ."-avg_{$column}";

        return $this->cache($tags)
            ->rememberForever($key, function () use ($column) {
                return parent::avg($column);
            });
    }

    public function count($columns = ['*'])
    {
        $tags = [str_slug(get_class($this->model))];
        $key = str_slug(get_class($this->model)) ."-count";

        return $this->cache($tags)
            ->rememberForever($key, function () use ($columns) {
                return parent::count($columns);
            });
    }

    public function cursor()
    {
        $tags = [str_slug(get_class($this->model))];
        $key = str_slug(get_class($this->model)) ."-cursor";

        return $this->cache($tags)
            ->rememberForever($key, function () {
                return collect(parent::cursor());
            });
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function find($id, $columns = ['*'])
    {
        $tags = $this->getCacheTags();
        $key = $this->getCacheKey($columns, $id);

        return $this->cache($tags)
            ->rememberForever($key, function () use ($id, $columns) {
                return parent::find($id, $columns);
            });
    }

    public function first($columns = ['*'])
    {
        $tags = $this->getCacheTags();
        $key = $this->getCacheKey($columns) . '-first';

        return $this->cache($tags)
            ->rememberForever($key, function () use ($columns) {
                return parent::first($columns);
            });
    }

    public function get($columns = ['*'])
    {
        $tags = $this->getCacheTags();
        $key = $this->getCacheKey($columns);

        return $this->cache($tags)
            ->rememberForever($key, function () use ($columns) {
                return parent::get($columns);
            });
    }

    public function max($column)
    {
        $tags = [str_slug(get_class($this->model))];
        $key = str_slug(get_class($this->model)) ."-max_{$column}";

        return $this->cache($tags)
            ->rememberForever($key, function () use ($column) {
                return parent::max($column);
            });
    }

    public function min($column)
    {
        $tags = [str_slug(get_class($this->model))];
        $key = str_slug(get_class($this->model)) ."-min_{$column}";

        return $this->cache($tags)
            ->rememberForever($key, function () use ($column) {
                return parent::min($column);
            });
    }

    public function pluck($column, $key = null)
    {
        $tags = $this->getCacheTags();
        $cacheKey = $this->getCacheKey([$column]) . "-pluck_{$column}";

        if ($key) {
            $cacheKey .= "_{$key}";
        }

        return $this->cache($tags)
            ->rememberForever($cacheKey, function () use ($column, $key) {
                return parent::pluck($column, $key);
            });
    }

    public function sum($column)
    {
        $tags = [str_slug(get_class($this->model))];
        $key = str_slug(get_class($this->model)) ."-sum_{$column}";

        return $this->cache($tags)
            ->rememberForever($key, function () use ($column) {
                return parent::sum($column);
            });
    }
}
