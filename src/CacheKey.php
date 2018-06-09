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
        if (in_array($where["type"], ["NotNull", "Null"])) {
            return "";
        }

        $values = $this->getValuesFromWhere($where);
        $values = $this->getValuesFromBindings($where, $values);



        return "_" . $values;
    }

    protected function getValuesFromWhere(array $where) : string
    {
        if (is_array(array_get($where, "values"))) {
            return implode("_", $where["values"]);
        }

        return array_get($where, "value", "");
    }

    protected function getValuesFromBindings(array $where, string $values) : string
    {
        if (! $values && ($this->query->bindings["where"][$this->currentBinding] ?? false)) {
            $values = $this->query->bindings["where"][$this->currentBinding];
            $this->currentBinding++;

            if ($where["type"] === "between") {
                $values .= "_" . $this->query->bindings["where"][$this->currentBinding];
                $this->currentBinding++;
            }
        }

        return $values ?: "";
    }

    protected function getWhereClauses(array $wheres = []) : string
    {
        return "" . $this->getWheres($wheres)
            ->reduce(function ($carry, $where) {
                $value = $carry;
                $value .= $this->getNestedClauses($where);
                $value .= $this->getColumnClauses($where);
                $value .= $this->getRawClauses($where);
                $value .= $this->getInClauses($where);
                $value .= $this->getNotInClauses($where);
                $value .= $this->getOtherClauses($where, $carry);

                return $value;
            });
    }

    protected function getNestedClauses(array $where) : string
    {
        if (! in_array($where["type"], ["Exists", "Nested", "NotExists"])) {
            return "";
        }

        $this->currentBinding++;

        return "-" . strtolower($where["type"]) . $this->getWhereClauses($where["query"]->wheres);
    }

    protected function getColumnClauses(array $where) : string
    {
        if ($where["type"] !== "Column") {
            return "";
        }

        $this->currentBinding++;

        return "-{$where["boolean"]}_{$where["first"]}_{$where["operator"]}_{$where["second"]}";
    }

    protected function getInClauses(array $where) : string
    {
        if (! in_array($where["type"], ["In"])) {
            return "";
        }

        $this->currentBinding++;
        $values = $this->recursiveImplode($where["values"], "_");

        return "-{$where["column"]}_in{$values}";
    }

    protected function getNotInClauses(array $where) : string
    {
        if (! in_array($where["type"], ["NotIn"])) {
            return "";
        }

        $this->currentBinding++;
        $values = $this->recursiveImplode($where["values"], "_");

        return "-{$where["column"]}_not_in{$values}";
    }

    protected function recursiveImplode(array $items, string $glue = ",") : string
    {
        $result = "";

        foreach ($items as $value) {
            if (is_array($value)) {
                $result .= $this->recursiveImplode($value, $glue);

                continue;
            }

            $result .= $glue . $value;
        }

        return $result;
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

        return "-" . str_replace(" ", "_", $clause);
    }

    protected function getOtherClauses(array $where) : string
    {
        if (in_array($where["type"], ["Exists", "Nested", "NotExists", "Column", "raw", "In", "NotIn"])) {
            return "";
        }

        $value = $this->getTypeClause($where);
        $value .= $this->getValuesClause($where);

        return "-{$where["column"]}_{$value}";
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
