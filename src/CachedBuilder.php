<?php namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use GeneaLabs\LaravelModelCaching\Traits\CacheKeyable;
use GeneaLabs\LaravelModelCaching\Traits\CacheTagable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class CachedBuilder extends EloquentBuilder
{
    use Cachable;
    use CacheKeyable;
    use CacheTagable;

    public function avg($column)
    {
        return $this->cache($this->makeCacheTags($this))
            ->rememberForever($this->makeCacheKey($this) . "-avg_{$column}", function () use ($column) {
                return parent::avg($column);
            });
    }

    public function count($columns = ['*'])
    {
        return $this->cache($this->makeCacheTags($this))
            ->rememberForever($this->makeCacheKey($this) . "-count", function () use ($columns) {
                return parent::count($columns);
            });
    }

    public function cursor()
    {
        return $this->cache($this->makeCacheTags($this))
            ->rememberForever($this->makeCacheKey($this) . "-cursor", function () {
                return collect(parent::cursor());
            });
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function find($id, $columns = ['*'])
    {
        return $this->cache($this->makeCacheTags($this))
            ->rememberForever($this->makeCacheKey($this, $columns, $id), function () use ($id, $columns) {
                return parent::find($id, $columns);
            });
    }

    public function first($columns = ['*'])
    {
        return $this->cache($this->makeCacheTags($this))
            ->rememberForever($this->makeCacheKey($this, $columns) . '-first', function () use ($columns) {
                return parent::first($columns);
            });
    }

    public function get($columns = ['*'])
    {
        return $this->cache($this->makeCacheTags($this))
            ->rememberForever($this->makeCacheKey($this, $columns), function () use ($columns) {
                return parent::get($columns);
            });
    }

    public function max($column)
    {
        return $this->cache($this->makeCacheTags($this))
            ->rememberForever($this->makeCacheKey($this) . "-max_{$column}", function () use ($column) {
                return parent::max($column);
            });
    }

    public function min($column)
    {
        return $this->cache($this->makeCacheTags($this))
            ->rememberForever($this->makeCacheKey($this) . "-min_{$column}", function () use ($column) {
                return parent::min($column);
            });
    }

    public function pluck($column, $key = null)
    {
        $cacheKey = $this->makeCacheKey($this, [$column]) . "-pluck_{$column}";

        if ($key) {
            $cacheKey .= "_{$key}";
        }

        return $this->cache($this->makeCacheTags($this))
            ->rememberForever($cacheKey, function () use ($column, $key) {
                return parent::pluck($column, $key);
            });
    }

    public function sum($column)
    {
        return $this->cache($this->makeCacheTags($this))
            ->rememberForever($this->makeCacheKey($this) . "-sum_{$column}", function () use ($column) {
                return parent::sum($column);
            });
    }
}
