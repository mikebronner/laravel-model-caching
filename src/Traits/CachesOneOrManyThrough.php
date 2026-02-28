<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use GeneaLabs\LaravelModelCaching\CacheKey;
use GeneaLabs\LaravelModelCaching\CacheTags;
use Illuminate\Pagination\Paginator;

trait CachesOneOrManyThrough
{
    use CachedValueRetrievable;

    public function get($columns = ['*'])
    {
        if (! $this->isCachable()) {
            return parent::get($columns);
        }

        $columns = collect($columns)->toArray();
        $cacheKey = $this->makeCacheKey($columns);

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function first($columns = ['*'])
    {
        if (! $this->isCachable()) {
            return parent::first($columns);
        }

        $columns = collect($columns)->toArray();
        $cacheKey = $this->makeCacheKey($columns, null, '-first');

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function find($id, $columns = ['*'])
    {
        if (! $this->isCachable()) {
            return parent::find($id, $columns);
        }

        $idKey = collect($id)->implode('_');
        $preStr = is_array($id) ? 'find_list' : 'find';
        $columns = collect($columns)->toArray();
        $cacheKey = $this->makeCacheKey($columns, null, "-{$preStr}_{$idKey}");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function count($columns = '*')
    {
        if (! $this->isCachable()) {
            return parent::count($columns);
        }

        $cacheKey = $this->makeCacheKey([$columns], null, '-count');

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null, $total = null)
    {
        if (! $this->isCachable()) {
            return parent::paginate($perPage, $columns, $pageName, $page);
        }

        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $columns = collect($columns)->toArray();
        $cacheKey = $this->makeCacheKey($columns, null, "-paginate_by_{$perPage}_{$pageName}_{$page}");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function pluck($column, $key = null)
    {
        if (! $this->isCachable()) {
            return parent::pluck($column, $key);
        }

        $keyDifferentiator = "-pluck_{$column}" . ($key ? "_{$key}" : '');
        $cacheKey = $this->makeCacheKey([$column], null, $keyDifferentiator);

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function avg($column)
    {
        if (! $this->isCachable()) {
            return parent::avg($column);
        }

        $cacheKey = $this->makeCacheKey(['*'], null, "-avg_{$column}");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function sum($column)
    {
        if (! $this->isCachable()) {
            return parent::sum($column);
        }

        $cacheKey = $this->makeCacheKey(['*'], null, "-sum_{$column}");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function min($column)
    {
        if (! $this->isCachable()) {
            return parent::min($column);
        }

        $cacheKey = $this->makeCacheKey(['*'], null, "-min_{$column}");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function max($column)
    {
        if (! $this->isCachable()) {
            return parent::max($column);
        }

        $cacheKey = $this->makeCacheKey(['*'], null, "-max_{$column}");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function exists()
    {
        if (! $this->isCachable()) {
            return parent::exists();
        }

        $cacheKey = $this->makeCacheKey(['*'], null, '-exists');

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    public function value($column)
    {
        if (! $this->isCachable()) {
            return parent::value($column);
        }

        $cacheKey = $this->makeCacheKey(['*'], null, "-value_{$column}");

        return $this->cachedValue(func_get_args(), $cacheKey);
    }

    protected function makeCacheKey(
        array $columns = ['*'],
        $idColumn = null,
        string $keyDifferentiator = ''
    ): string {
        $eagerLoad = $this->eagerLoad ?? [];
        $model = $this->getModel();
        $query = $this->getQuery()->getQuery();

        return (new CacheKey(
            $eagerLoad,
            $model,
            $query,
            $this->macroKey,
            $this->withoutGlobalScopes,
            $this->withoutAllGlobalScopes
        ))->make($columns, $idColumn, $keyDifferentiator);
    }

    protected function makeCacheTags(): array
    {
        $eagerLoad = $this->eagerLoad ?? [];
        $model = $this->getModel();
        $query = $this->getQuery()->getQuery();

        return (new CacheTags($eagerLoad, $model, $query))->make();
    }
}