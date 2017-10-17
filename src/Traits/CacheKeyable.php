<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use GeneaLabs\LaravelModelCaching\CachedBuilder;
use Illuminate\Support\Collection;

trait CacheKeyable
{
    protected function makeCacheKey(
        CachedBuilder $builder,
        array $columns = ['*'],
        $idColumn = null
    ) : string {
        $key = $this->getModelSlug($builder);
        $key .= $this->getIdColumn($idColumn ?: '');
        $key .= $this->getQueryColumns($columns);
        $key .= $this->getWhereClauses($builder);
        $key .= $this->getWithModels($builder);
        $key .= $this->getOrderByClauses($builder);
        $key .= $this->getOffsetClause($builder);
        $key .= $this->getLimitClause($builder);

        return $key;
    }

    protected function getIdColumn(string $idColumn) : string
    {
        return $idColumn ? "_{$idColumn}" : '';
    }

    protected function getLimitClause(CachedBuilder $builder) : string
    {
        if (! $builder->query->limit) {
            return '';
        }

        return "-limit_{$builder->query->limit}";
    }

    protected function getModelSlug(CachedBuilder $builder) : string
    {
        return str_slug(get_class($builder->model));
    }

    protected function getOffsetClause(CachedBuilder $builder) : string
    {
        if (! $builder->query->offset) {
            return '';
        }

        return "-offset_{$builder->query->offset}";
    }

    protected function getOrderByClauses(CachedBuilder $builder) : string
    {
        $orders = collect($builder->query->orders);

        return $orders->reduce(function($carry, $order){
            return $carry . '_orderBy_' . $order['column'] . '_' . $order['direction'];
        })
        ?: '';
    }

    protected function getQueryColumns(array $columns) : string
    {
        if ($columns === ['*'] || $columns === []) {
            return '';
        }

        return '_' . implode('_', $columns);
    }

    protected function getTypeClause($where) : string
    {
        return in_array($where['type'], ['In', 'Null', 'NotNull'])
            ? strtolower($where['type'])
            : '';
    }

    protected function getValuesClause(array $where = null) : string
    {
        return is_array(array_get($where, 'values'))
            ? '_' . implode('_', $where['values'])
            : '';
    }

    protected function getWhereClauses(CachedBuilder $builder, array $wheres = []) : string
    {
        return $this->getWheres($builder, $wheres)
            ->reduce(function ($carry, $where) use ($builder) {
                if (in_array($where['type'], ['Exists', 'Nested', 'NotExists'])) {
                    return '_' . strtolower($where['type']) . $this->getWhereClauses($builder, $where['query']->wheres);
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
            })
            . '';
    }

    protected function getWheres(CachedBuilder $builder, array $wheres) : Collection
    {
        $wheres = collect($wheres);

        if ($wheres->isEmpty()) {
            $wheres = collect($builder->query->wheres);
        }

        return $wheres;
    }

    protected function getWithModels(CachedBuilder $builder) : string
    {
        $eagerLoads = collect($builder->eagerLoad);

        if ($eagerLoads->isEmpty()) {
            return '';
        }

        return '-' . implode('-', $eagerLoads->keys()->toArray());
    }
}
