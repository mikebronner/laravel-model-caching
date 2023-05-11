<?php namespace GeneaLabs\LaravelModelCaching;

use Exception;
use GeneaLabs\LaravelModelCaching\Traits\CachePrefixing;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class CacheKey
{
    use CachePrefixing;

    protected $currentBinding = 0;
    protected $eagerLoad;
    protected $macroKey;
    protected $model;
    protected $query;
    protected $withoutGlobalScopes = [];
    protected $withoutAllGlobalScopes = false;

    public function __construct(
        array $eagerLoad,
        $model,
        $query,
        $macroKey,
        array $withoutGlobalScopes,
        $withoutAllGlobalScopes
    ) {
        $this->eagerLoad = $eagerLoad;
        $this->macroKey = $macroKey;
        $this->model = $model;
        $this->query = $query;
        $this->withoutGlobalScopes = $withoutGlobalScopes;
        $this->withoutAllGlobalScopes = $withoutAllGlobalScopes;
    }

    public function make(
        array $columns = ["*"],
        $idColumn = null,
        string $keyDifferentiator = ""
    ) : string {
        $key = $this->getCachePrefix();
        $key .= $this->getTableSlug();
        $key .= $this->getModelSlug();
        $key .= $this->getIdColumn($idColumn ?: "");
        $key .= $this->getQueryColumns($columns);
        $key .= $this->getWhereClauses();
        $key .= $this->getWithModels();
        $key .= $this->getOrderByClauses();
        $key .= $this->getOffsetClause();
        $key .= $this->getLimitClause();
        $key .= $this->getBindingsSlug();
        $key .= $keyDifferentiator;
        $key .= $this->macroKey;
// dump($key);
        return $key;
    }

    protected function getBindingsSlug() : string
    {
        if (! method_exists($this->model, 'query')) {
            return '';
        }

        if ($this->withoutAllGlobalScopes) {
            return Arr::query($this->model->query()->withoutGlobalScopes()->getBindings());
        }

        if (count($this->withoutGlobalScopes) > 0) {
            return Arr::query($this->model->query()->withoutGlobalScopes($this->withoutGlobalScopes)->getBindings());
        }

        return Arr::query($this->model->query()->getBindings());
    }

    protected function getColumnClauses(array $where) : string
    {
        if ($where["type"] !== "Column") {
            return "";
        }
        
        if ($where["first"] instanceof Expression) {
            $where["first"] = $this->expressionToString($where["first"]);
        }

        if ($where["second"] instanceof Expression) {
	    $where["second"] = $this->expressionToString($where["second"]);
        }

        return "-{$where["boolean"]}_{$where["first"]}_{$where["operator"]}_{$where["second"]}";
    }

    protected function getCurrentBinding(string $type, $bindingFallback = null)
    {
        return data_get($this->query->bindings, "{$type}.{$this->currentBinding}", $bindingFallback);
    }

    protected function getIdColumn(string $idColumn) : string
    {
        return $idColumn ? "_{$idColumn}" : "";
    }

    protected function getInAndNotInClauses(array $where) : string
    {
        if (! in_array($where["type"], ["In", "NotIn", "InRaw"])) {
            return "";
        }

        $type = strtolower($where["type"]);
        $subquery = $this->getValuesFromWhere($where);
        $values = collect($this->getCurrentBinding('where', []));

        if (Str::startsWith($subquery, $values->first())) {
            $this->currentBinding += count($where["values"]);
        }

        if (! is_numeric($subquery) && ! is_numeric(str_replace("_", "", $subquery))) {
            try {
                $subquery = Uuid::fromBytes($subquery);
                $values = $this->recursiveImplode([$subquery], "_");

                return "-{$where["column"]}_{$type}{$values}";
            } catch (Exception $exception) {
                // do nothing
            }
        }

        $subquery = preg_replace('/\?(?=(?:[^"]*"[^"]*")*[^"]*\Z)/m', "_??_", $subquery);
        $subquery = collect(vsprintf(str_replace("_??_", "%s", $subquery), $values->toArray()));
        $values = $this->recursiveImplode($subquery->toArray(), "_");

        return "-{$where["column"]}_{$type}{$values}";
    }

    protected function getLimitClause() : string
    {
        if (! property_exists($this->query, "limit")
            || ! $this->query->limit
        ) {
            return "";
        }

        return "-limit_{$this->query->limit}";
    }

    protected function getModelSlug() : string
    {
        return (new Str)->slug(get_class($this->model));
    }

    protected function getNestedClauses(array $where) : string
    {
        if (! in_array($where["type"], ["Exists", "Nested", "NotExists"])) {
            return "";
        }

        return "-" . strtolower($where["type"]) . $this->getWhereClauses($where["query"]->wheres);
    }

    protected function getOffsetClause() : string
    {
        if (! property_exists($this->query, "offset")
            || ! $this->query->offset
        ) {
            return "";
        }

        return "-offset_{$this->query->offset}";
    }

    protected function getOrderByClauses() : string
    {
        if (! property_exists($this->query, "orders")
            || ! $this->query->orders
        ) {
            return "";
        }

        $orders = collect($this->query->orders);
        
        return $orders
            ->reduce(function ($carry, $order) {
                if (($order["type"] ?? "") === "Raw") {
                    return $carry . "_orderByRaw_" . (new Str)->slug($order["sql"]);
                }

                return sprintf(
                    '%s_orderBy_%s_%s',
                    $carry,
                    $this->expressionToString($order["column"]),
                    $order["direction"]
                );
            })
            ?: "";
    }

    protected function getOtherClauses(array $where) : string
    {
        if (in_array($where["type"], ["Exists", "Nested", "NotExists", "Column", "raw", "In", "NotIn", "InRaw"])) {
            return "";
        }

        $value = $this->getTypeClause($where);
        $value .= $this->getValuesClause($where);

        $column = "";

	if (isset($where["column"]) && $where["column"] instanceof Expression) {
            $where["column"] = $this->expressionToString($where["column"]);
        }    

        $column .= isset($where["column"]) ? $where["column"] : "";
        $column .= isset($where["columns"]) ? implode("-", $where["columns"]) : "";

        return "-{$column}_{$value}";
    }

    protected function getQueryColumns(array $columns) : string
    {
        if (($columns === ["*"]
                || $columns === [])
            && (! property_exists($this->query, "columns")
                || ! $this->query->columns)
        ) {
            return "";
        }

        if (property_exists($this->query, "columns")
            && $this->query->columns
        ) {
            $columns = array_map(function ($column) {                
                return $this->expressionToString($column);
            }, $this->query->columns);

            return "_" . implode("_", $columns);
        }

        return "_" . implode("_", $columns);
    }

    protected function getRawClauses(array $where) : string
    {
        if (! in_array($where["type"], ["raw"])) {
            return "";
        }

        $queryParts = explode("?", $where["sql"]);
        $clause = "_{$where["boolean"]}";

        while (count($queryParts) > 1) {
            $clause .= "_" . array_shift($queryParts);
            $clause .= $this->getCurrentBinding("where");
            $this->currentBinding++;
        }

        $lastPart = array_shift($queryParts);

        if ($lastPart) {
            $clause .= "_" . $lastPart;
        }

        return "-" . str_replace(" ", "_", $clause);
    }

    protected function getTableSlug() : string
    {
        return (new Str)->slug($this->query->from)
            . ":";
    }

    protected function getTypeClause($where) : string
    {
        $type = in_array($where["type"], ["InRaw", "In", "NotIn", "Null", "NotNull", "between", "NotInSub", "InSub", "JsonContains", "Fulltext"])
            ? strtolower($where["type"])
            : strtolower($where["operator"]);

        return str_replace(" ", "_", $type);
    }

    protected function getValuesClause(array $where = []) : string
    {
        if (! $where
            || in_array($where["type"], ["NotNull", "Null"])
        ) {
            return "";
        }

        $values = $this->getValuesFromWhere($where);
        $values = $this->getValuesFromBindings($where, $values);

        return "_" . $values;
    }

    protected function getValuesFromWhere(array $where) : string
    {
        if (array_key_exists("value", $where)
            && is_object($where["value"])
            && get_class($where["value"]) === "DateTime"
        ) {
            return $where["value"]->format("Y-m-d-H-i-s");
        }

        if (is_array((new Arr)->get($where, "values"))) {
            $values = collect($where["values"])->flatten()->toArray();
            return implode("_", $this->processEnums($values));
        }

        if (is_array((new Arr)->get($where, "value"))) {
            $values = collect($where["value"])->flatten()->toArray();
            return implode("_", $this->processEnums($values));
        }

        $value = (new Arr)->get($where, "value", "");

        return $this->processEnum($value);
    }

    protected function getValuesFromBindings(array $where, string $values) : string
    {
        $bindingFallback = __CLASS__ . ':UNKNOWN_BINDING';
        $currentBinding = $this->getCurrentBinding("where", $bindingFallback);

        if ($currentBinding !== $bindingFallback) {
            $values = $currentBinding;
            $this->currentBinding++;

            if ($where["type"] === "between") {
                $values .= "_" . $this->getCurrentBinding("where");
                $this->currentBinding++;
            }
        }

        if (is_object($values)
            && get_class($values) === "DateTime"
        ) {
            $values = $values->format("Y-m-d-H-i-s");
        }

        return $values;
    }

    protected function getWhereClauses(array $wheres = []) : string
    {
        return "" . $this->getWheres($wheres)
            ->reduce(function ($carry, $where) {
                $value = $carry;
                $value .= $this->getNestedClauses($where);
                $value .= $this->getColumnClauses($where);
                $value .= $this->getRawClauses($where);
                $value .= $this->getInAndNotInClauses($where);
                $value .= $this->getOtherClauses($where);

                return $value;
            });
    }
    
    protected function getWheres(array $wheres) : Collection
    {
        $wheres = collect($wheres);

        if ($wheres->isEmpty()
            && property_exists($this->query, "wheres")
        ) {
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

        return $eagerLoads->keys()->reduce(function ($carry, $related) {
            if (! method_exists($this->model, $related)) {
                return "{$carry}-{$related}";
            }

            $relatedModel = $this->model->$related()->getRelated();
            $relatedConnection = $relatedModel->getConnection()->getName();
            $relatedDatabase = $relatedModel->getConnection()->getDatabaseName();

            return "{$carry}-{$relatedConnection}:{$relatedDatabase}:{$related}";
        });
    }
   
    protected function recursiveImplode(array $items, string $glue = ",") : string
    {
        $result = "";

        foreach ($items as $value) {
            if (is_string($value)) {
                $value = str_replace('"', '', $value);
                $value = explode(" ", $value);

                if (count($value) === 1) {
                    $value = $value[0];
                }
            }

            if (is_array($value)) {
                $result .= $this->recursiveImplode($value, $glue);

                continue;
            }

            $result .= $glue . $value;
        }

        return $result;
    }

    private function processEnum(\BackedEnum|\UnitEnum|Expression|string $value): string
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        } elseif ($value instanceof \UnitEnum) {
            return $value->name;
        } elseif ($value instanceof Expression) {
            return $this->expressionToString($value);
        }

        return $value;
    }

    private function processEnums(array $values): array
    {
        return array_map(fn($value) => $this->processEnum($value), $values);
    }

    private function expressionToString(Expression|string $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        return $value->getValue($this->query->getConnection()->getQueryGrammar());
    }
}
