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
        $key .= $this->getOrderByClauses();
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

    protected function getWhereClauses(array $wheres = []) : string
    {
        $wheres = collect($wheres);

        if ($wheres->isEmpty()) {
            $wheres = collect($this->query->wheres);
        }

        return $wheres->reduce(function ($carry, $where) {
            if (in_array($where['type'], ['Exists', 'Nested', 'NotExists'])) {
                return '_' . strtolower($where['type']) . $this->getWhereClauses($where['query']->wheres);
            }

            if ($where['type'] === 'Column') {
                return "_{$where['boolean']}_{$where['first']}_{$where['operator']}_{$where['second']}";
            }

            if ($where['type'] === 'raw') {
                return "_{$where['boolean']}_" . str_slug($where['sql']);
            }

            $value = array_get($where, 'value');
            $value .= $this->getTypeClause($where);
            $value .= $this->getValuesClause($where);

            return "{$carry}-{$where['column']}_{$value}";
        }) . '';
    }

    protected function getWithModels() : string
    {
        $eagerLoads = collect($this->eagerLoad);

        if ($eagerLoads->isEmpty()) {
            return '';
        }

        return '-' . implode('-', $eagerLoads->keys()->toArray());
    }

	protected function getOrderByClauses(){
        $orders = collect($this->query->orders);

        return $orders->reduce(function($carry, $order){
            return $carry . '_orderBy_' . $order['column'] . '_' . $order['direction'];
        });
    }

    protected function getMethodKey(string $postfix = null) : string
    {
        return str_slug(get_class($this->model)) . $postfix;
    }

    protected function getModelTag() : array
    {
        return [str_slug(get_class($this->model))];
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
        return $this->cache($this->getModelTag())
            ->rememberForever($this->getMethodKey("-avg_{$column}"), function () use ($column) {
                return parent::avg($column);
            });
    }

    public function count($columns = ['*'])
    {
        return $this->cache($this->getModelTag())
            ->rememberForever($this->getMethodKey("-count"), function () use ($columns) {
                return parent::count($columns);
            });
    }

    public function cursor()
    {
        return $this->cache($this->getModelTag())
            ->rememberForever($this->getMethodKey("-cursor"), function () {
                return collect(parent::cursor());
            });
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function find($id, $columns = ['*'])
    {
        return $this->cache($this->getCacheTags())
            ->rememberForever($this->getCacheKey($columns, $id), function () use ($id, $columns) {
                return parent::find($id, $columns);
            });
    }

    public function first($columns = ['*'])
    {
        return $this->cache($this->getCacheTags())
            ->rememberForever($this->getCacheKey($columns) . '-first', function () use ($columns) {
                return parent::first($columns);
            });
    }

    public function get($columns = ['*'])
    {
        return $this->cache($this->getCacheTags())
            ->rememberForever($this->getCacheKey($columns), function () use ($columns) {
                return parent::get($columns);
            });
    }

    public function max($column)
    {
        return $this->cache($this->getModelTag())
            ->rememberForever($this->getMethodKey("-max_{$column}"), function () use ($column) {
                return parent::max($column);
            });
    }

    public function min($column)
    {
        return $this->cache($this->getModelTag())
            ->rememberForever($this->getMethodKey("-min_{$column}"), function () use ($column) {
                return parent::min($column);
            });
    }

    public function pluck($column, $key = null)
    {
        $cacheKey = $this->getCacheKey([$column]) . "-pluck_{$column}";

        if ($key) {
            $cacheKey .= "_{$key}";
        }

        return $this->cache($this->getCacheTags())
            ->rememberForever($cacheKey, function () use ($column, $key) {
                return parent::pluck($column, $key);
            });
    }

    public function sum($column)
    {
        return $this->cache($this->getModelTag())
            ->rememberForever($this->getMethodKey("-sum_{$column}"), function () use ($column) {
                return parent::sum($column);
            });
    }

    protected function getTypeClause($where)
    {
        return in_array($where['type'], ['In', 'Null', 'NotNull'])
            ? strtolower($where['type'])
            : '';
    }

    protected function getValuesClause($where)
    {
        return is_array(array_get($where, 'values'))
            ? '_' . implode('_', $where['values'])
            : '';
    }
}
