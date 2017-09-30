<?php namespace GeneaLabs\LaravelModelCaching;

use Closure;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

class CachedBuilder extends EloquentBuilder
{
    protected function cache(array $tags = [])
    {
        $cache = cache();

        if (is_subclass_of($cache->getStore(), TaggableStore::class)) {
            $cache = $cache->tags($tags);
        }

        return $cache;
    }

    protected function getCacheKey(array $columns = ['*'], $idColumn = null) : string
    {
        $key = $this->getModelSlug();
        $key .= $this->getIdColumn($idColumn ?: '');
        $key .= $this->getQueryColumns($columns);
        $key .= $this->getWhereClauses();
        $key .= $this->getWithModels();
        $key .= $this->getOffsetClause();
        $key .= $this->getLimitClause();

        return $key;
    }

    protected function getIdColumn(string $idColumn) : string
    {
        return $idColumn ? "_{$idColumn}" : '';
    }

    protected function getLimitClause() : string
    {
        if (! $this->query->limit) {
            return '';
        }

        return "-limit_{$this->query->limit}";
    }

    protected function getModelSlug() : string
    {
        return str_slug(get_class($this->model));
    }

    protected function getOffsetClause() : string
    {
        if (! $this->query->offset) {
            return '';
        }

        return "-offset_{$this->query->offset}";
    }

    protected function getQueryColumns(array $columns) : string
    {
        if ($columns === ['*'] || $columns === []) {
            return '';
        }

        return '_' . implode('_', $columns);
    }

    protected function getWhereClauses() : string
    {
        return collect($this->query->wheres)->reduce(function ($carry, $where) {
            $value = $where['value'] ?? implode('_', ($where['values'] ?? []));

            if (! $value) {
                return $carry . '';
            }

            return "{$carry}-{$where['column']}_{$value}";
        }) ?: '';
    }

    protected function getWithModels() : string
    {
        $eagerLoads = collect($this->eagerLoad);

        if ($eagerLoads->isEmpty()) {
            return '';
        }

        return '-' . implode('-', $eagerLoads->keys()->toArray());
    }

    protected function getCacheTags() : array
    {
        return collect($this->eagerLoad)->keys()
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
