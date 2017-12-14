<?php namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class CachedBuilder extends EloquentBuilder
{
    use Cachable;

    public function avg($column)
    {
        return $this->cache($this->makeCacheTags())
            ->rememberForever($this->makeCacheKey() . "-avg_{$column}", function () use ($column) {
                return parent::avg($column);
            });
    }

    public function count($columns = ['*'])
    {
        return $this->cache($this->makeCacheTags())
            ->rememberForever($this->makeCacheKey() . "-count", function () use ($columns) {
                return parent::count($columns);
            });
    }

    public function cursor()
    {
        return $this->cache($this->makeCacheTags())
            ->rememberForever($this->makeCacheKey() . "-cursor", function () {
                return collect(parent::cursor());
            });
    }

    public function delete()
    {
        $this->cache($this->makeCacheTags())
            ->flush();

        return parent::delete();
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function find($id, $columns = ['*'])
    {
        return $this->cache($this->makeCacheTags())
            ->rememberForever($this->makeCacheKey($columns, $id), function () use ($id, $columns) {
                return parent::find($id, $columns);
            });
    }

    public function first($columns = ['*'])
    {
        return $this->cache($this->makeCacheTags())
            ->rememberForever($this->makeCacheKey($columns) . '-first', function () use ($columns) {
                return parent::first($columns);
            });
    }

    public function get($columns = ['*'])
    {
        return $this->cache($this->makeCacheTags())
            ->rememberForever($this->makeCacheKey($columns), function () use ($columns) {
                return parent::get($columns);
            });
    }

    public function max($column)
    {
        return $this->cache($this->makeCacheTags())
            ->rememberForever($this->makeCacheKey() . "-max_{$column}", function () use ($column) {
                return parent::max($column);
            });
    }

    public function min($column)
    {
        return $this->cache($this->makeCacheTags())
            ->rememberForever($this->makeCacheKey() . "-min_{$column}", function () use ($column) {
                return parent::min($column);
            });
    }

    public function pluck($column, $key = null)
    {
        $cacheKey = $this->makeCacheKey([$column]) . "-pluck_{$column}";

        if ($key) {
            $cacheKey .= "_{$key}";
        }

        return $this->cache($this->makeCacheTags())
            ->rememberForever($cacheKey, function () use ($column, $key) {
                return parent::pluck($column, $key);
            });
    }

    public function sum($column)
    {
        return $this->cache($this->makeCacheTags())
            ->rememberForever($this->makeCacheKey() . "-sum_{$column}", function () use ($column) {
                return parent::sum($column);
            });
    }

    protected function makeCacheKey(array $columns = ['*'], $idColumn = null) : string
    {
        return (new CacheKey($this->eagerLoad, $this->model, $this->query))
            ->make($columns, $idColumn);
    }

    protected function makeCacheTags() : array
    {
        return (new CacheTags($this->eagerLoad, $this->model))
            ->make();
    }
}
