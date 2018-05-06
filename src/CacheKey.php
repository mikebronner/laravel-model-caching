<?php namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\Traits\CachePrefixing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class CacheKey
{
    use CachePrefixing;

    protected $eagerLoad;
    protected $model;
    protected $query;
    protected $currentBinding = 0;

    public function __construct(
        array $eagerLoad,
        Model $model,
        Builder $query
    ) {
        $this->eagerLoad = $eagerLoad;
        $this->model = $model;
        $this->query = $query;
    }

    public function make(
        array $columns = ["*"],
        $idColumn = null,
        string $keyDifferentiator = ""
    ) : string {
        $key = $this->getCachePrefix();
        $key .= $this->getModelSlug();
        $key .= $this->getIdColumn($idColumn ?: "");
        $key .= $this->getQueryColumns($columns);
        $key .= $this->getWhereClauses();
        $key .= $this->getWithModels();
        $key .= $this->getOrderByClauses();
        $key .= $this->getOffsetClause();
        $key .= $this->getLimitClause();
        $key .= $keyDifferentiator;

        return $key;
    }

    protected function getIdColumn(string $idColumn) : string
    {
        return $idColumn ? "_{$idColumn}" : "";
    }

    protected function getLimitClause() : string
    {
        if (! $this->query->limit) {
            return "";
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
            return "";
        }

        return "-offset_{$this->query->offset}";
    }

    protected function getOrderByClauses() : string
    {
        $orders = collect($this->query->orders);

        return $orders
            ->reduce(function ($carry, $order) {
                if (($order["type"] ?? "") === "Raw") {
                    return $carry . "_orderByRaw_" . str_slug($order["sql"]);
                }

                return $carry . "_orderBy_" . $order["column"] . "_" . $order["direction"];
            })
            ?: "";
    }

    protected function getQueryColumns(array $columns) : string
    {
        if ($columns === ["*"] || $columns === []) {
            return "";
        }

        return "_" . implode("_", $columns);
    }

    protected function getTypeClause($where) : string
    {
        $type =in_array($where["type"], ["In", "NotIn", "Null", "NotNull", "between"])
            ? strtolower($where["type"])
            : strtolower($where["operator"]);

        return str_replace(" ", "_", $type);
    }

    protected function getValuesClause(array $where = null) : string
    {
        if (in_array($where["type"], ["NotNull"])) {
            return "";
        }

        $values = $this->getValuesFromWhere($where);
        $values = $this->getValuesFromBindings($values);

        return "_" . $values;
    }

    protected function getValuesFromWhere(array $where) : string
    {
        return is_array(array_get($where, "values"))
            ? implode("_", $where["values"])
            : "";
    }

    protected function getValuesFromBindings(string $values) : string
    {
        if (! $values && $this->query->bindings["where"] ?? false) {
            $values = $this->query->bindings["where"][$this->currentBinding];
        }

        return $values;
    }

    protected function getWhereClauses(array $wheres = []) : string
    {
        $whereClause = $this->getWheres($wheres)
            ->reduce(function ($carry, $where) {
                $value = $carry;
                $value .= $this->getNestedClauses($where);
                $value .= $this->getColumnClauses($where);
                $value .= $this->getRawClauses($where);
                $value .= $this->getOtherClauses($where, $carry);

                return $value;
            })
            . "";

        return $whereClause;
    }

    protected function getNestedClauses(array $where) : string
    {
        if (! in_array($where["type"], ["Exists", "Nested", "NotExists"])) {
            return "";
        }

        $this->currentBinding++;

        return "_" . strtolower($where["type"]) . $this->getWhereClauses($where["query"]->wheres);
    }

    protected function getColumnClauses(array $where) : string
    {
        if ($where["type"] !== "Column") {
            return "";
        }

        $this->currentBinding++;

        return "_{$where["boolean"]}_{$where["first"]}_{$where["operator"]}_{$where["second"]}";
    }

    protected function getRawClauses(array $where) : string
    {
        if ($where["type"] !== "raw") {
            return "";
        }

        $queryParts = explode("?", $where["sql"]);
        $clause = "_{$where["boolean"]}";

        while (count($queryParts) > 1) {
            $clause .= "_" . array_shift($queryParts);
            $clause .= $this->query->bindings["where"][$this->currentBinding];
            $this->currentBinding++;
        }

        $lastPart = array_shift($queryParts);

        if ($lastPart) {
            $clause .= "_" . $lastPart;
        }

        return str_replace(" ", "_", $clause);
    }

    protected function getOtherClauses(array $where, string $carry = null) : string
    {
        if (in_array($where["type"], ["Exists", "Nested", "NotExists", "raw", "Column"])) {
            return "";
        }

        $value = $this->getTypeClause($where);
        $value .= $this->getValuesClause($where);
        $this->currentBinding++;

        return "{$where["column"]}_{$value}";
    }

    protected function getWheres(array $wheres) : Collection
    {
        $wheres = collect($wheres);

        if ($wheres->isEmpty()) {
            $wheres = collect($this->query->wheres);
        }

        return $wheres;
    }

    protected function getWithModels() : string
    {
        $eagerLoads = collect($this->eagerLoad);

        if ($eagerLoads->isEmpty()) {
            return "";
        }

        return "-" . implode("-", $eagerLoads->keys()->toArray());
    }
}
