<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
trait Buildable
{
    use CachedValueRetrievable;

    public function avg($column)
    {
        if (! $this->isCachable()) {
            return parent::avg($column);
        }

        $cacheKey = $this->makeCacheKey(["*"], null, "-avg_{$column}");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function count($columns = "*")
    {
        if (! $this->isCachable()) {
            return parent::count($columns);
        }

        $cacheKey = $this->makeCacheKey([$columns], null, "-count");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function exists()
    {
        if (! $this->isCachable()) {
            return parent::exists();
        }

        $cacheKey = $this->makeCacheKey(['*'], null, "-exists");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function decrement($column, $amount = 1, array $extra = [])
    {
        try {
            $this->cache($this->makeCacheTags())
                ->flush();
        } catch (\Exception $exception) {
            if (! $this->shouldFallbackToDatabase()) {
                throw $exception;
            }

            Log::warning("laravel-model-caching: cache flush failed during decrement — {$exception->getMessage()}");
        }

        return parent::decrement($column, $amount, $extra);
    }

    public function delete()
    {
        try {
            $this->cache($this->makeCacheTags())
                ->flush();
        } catch (\Exception $exception) {
            if (! $this->shouldFallbackToDatabase()) {
                throw $exception;
            }

            Log::warning("laravel-model-caching: cache flush failed during delete — {$exception->getMessage()}");
        }

        return parent::delete();
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function find($id, $columns = ["*"])
    {
        if (! $this->isCachable()) {
            return parent::find($id, $columns);
        }

        $idKey = collect($id)
            ->implode('_');
        $preStr = is_array($id)
            ? 'find_list'
            : 'find';
        $columns = collect($columns)->toArray();
        $cacheKey = $this->makeCacheKey($columns, null, "-{$preStr}_{$idKey}");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function first($columns = ["*"])
    {
        if (! $this->isCachable()) {
            return parent::first($columns);
        }

        $columns = collect($columns)->toArray();
        $cacheKey = $this->makeCacheKey($columns, null, "-first");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function forceDelete()
    {
        try {
            $this->cache($this->makeCacheTags())
                ->flush();
        } catch (\Exception $exception) {
            if (! $this->shouldFallbackToDatabase()) {
                throw $exception;
            }

            Log::warning("laravel-model-caching: cache flush failed during forceDelete — {$exception->getMessage()}");
        }

        return parent::forceDelete();
    }

    public function get($columns = ["*"])
    {
        if (! $this->isCachable()) {
            return parent::get($columns);
        }

        $columns = collect($columns)->toArray();
        $cacheKey = $this->makeCacheKey($columns);

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function increment($column, $amount = 1, array $extra = [])
    {
        try {
            $this->cache($this->makeCacheTags())
                ->flush();
        } catch (\Exception $exception) {
            if (! $this->shouldFallbackToDatabase()) {
                throw $exception;
            }

            Log::warning("laravel-model-caching: cache flush failed during increment — {$exception->getMessage()}");
        }

        return parent::increment($column, $amount, $extra);
    }

    public function inRandomOrder($seed = '')
    {
        $this->isCachable = false;

        return parent::inRandomOrder($seed);
    }

    public function insert(array $values)
    {
        if (property_exists($this, "model")) {
            $this->checkCooldownAndFlushAfterPersisting($this->model);
        }

        return parent::insert($values);
    }

    public function max($column)
    {
        if (! $this->isCachable()) {
            return parent::max($column);
        }

        $cacheKey = $this->makeCacheKey(["*"], null, "-max_{$column}");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function min($column)
    {
        if (! $this->isCachable()) {
            return parent::min($column);
        }

        $cacheKey = $this->makeCacheKey(["*"], null, "-min_{$column}");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function paginate(
        $perPage = null,
        $columns = ["*"],
        $pageName = "page",
        $page = null,
        $total = null
    ) {
        if (! $this->isCachable()) {
            return parent::paginate($perPage, $columns, $pageName, $page);
        }

        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        if (is_array($page)) {
            $page = $this->recursiveImplodeWithKey($page);
        }

        $columns = collect($columns)->toArray();
        $keyDifferentiator = "-paginate_by_{$perPage}_{$pageName}_{$page}";

        if ($total !== null) {
            $total = value($total);
            $keyDifferentiator .= $total !== null
                ? "_{$total}"
                : "";
        }

        $cacheKey = $this->makeCacheKey($columns, null, $keyDifferentiator);

        $result = $this->cachedValue(func_get_args(), $cacheKey);

        if ($result instanceof \Illuminate\Pagination\AbstractPaginator) {
            $result->setPath(Paginator::resolveCurrentPath());
        }

        return $result;
    }

    protected function recursiveImplodeWithKey(array $items, string $glue = "_") : string
    {
        $result = "";

        foreach ($items as $key => $value) {
            $result .= $glue . $key . $glue . $value;
        }

        return $result;
    }

    public function pluck($column, $key = null)
    {
        if (! $this->isCachable()) {
            return parent::pluck($column, $key);
        }

        $keyDifferentiator = "-pluck_{$column}" . ($key ? "_{$key}" : "");
        $cacheKey = $this->makeCacheKey([$column], null, $keyDifferentiator);

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function sum($column)
    {
        if (! $this->isCachable()) {
            return parent::sum($column);
        }

        $cacheKey = $this->makeCacheKey(["*"], null, "-sum_{$column}");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function update(array $values)
    {
        if (property_exists($this, "model")) {
            $this->checkCooldownAndFlushAfterPersisting($this->model);
        }

        return parent::update($values);
    }

    public function value($column)
    {
        if (! $this->isCachable()) {
            return parent::value($column);
        }

        $cacheKey = $this->makeCacheKey(["*"], null, "-value_{$column}");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

}
