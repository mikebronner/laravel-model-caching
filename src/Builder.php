<?php namespace GeneaLabs\LaravelModelCaching;

use Closure;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

class Builder extends EloquentBuilder
{
    protected function cache(array $tags = [])
    {
        $cache = cache();

        if (is_subclass_of($cache->getStore(), TaggableStore::class)) {
            $cache = $cache->tags($tags);
        }

        return $cache;
    }

    protected function cacheResults(Relation $relation, array $models, string $name) : array
    {
        $parentIds = implode('_', collect($models)->pluck('id')->toArray());
        $parentName = str_slug(get_class($relation->getParent()));
        $childName = str_slug(get_class($relation->getRelated()));

        $cachedResults = $this->cache([$parentName, $childName])->rememberForever(
            "{$parentName}_{$parentIds}-{$childName}s",
            function () use ($relation, $models, $name) {
                return $relation->match(
                   $relation->initRelation($models, $name),
                   $relation->getEager(),
                   $name
               );
            }
        );

        return $cachedResults;
    }

    protected function eagerLoadRelation(array $models, $name, Closure $constraints)
    {
        $relation = $this->getRelation($name);
        $relation->addEagerConstraints($models);
        $constraints($relation);

        return $this->cacheResults($relation, $models, $name);
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function find($id, $columns = ['*'])
    {
        $tag = str_slug(get_class($this->model));
        $key = "{$tag}_{$id}_" . implode('_', $columns);

        return $this->cache([$tag])
            ->rememberForever($key, function () use ($id, $columns) {
                return parent::find($id, $columns);
            });
    }

    public function count($columns = '*')
    {
        $tag = str_slug(get_class($this->model));
        $key = "{$tag}_" . implode('_', $columns);

        return $this->cache([$tag])
            ->rememberForever($key, function () use ($id, $columns) {
                return parent::count($columns);
            });
    }

    public function first($columns = ['*'])
    {
        $tag = str_slug(get_class($this->model));
        $key = "{$tag}_" . implode('_', $columns);

        return $this->cache([$tag])
            ->rememberForever($key, function () use ($id, $columns) {
                return parent::first($columns);
            });
    }

    public function get($columns = ['*'])
    {
        $tag = str_slug(get_class($this->model));
        $key = "{$tag}_" . implode('_', $columns);
        $key .= collect($this->query->wheres)->reduce(function ($carry, $where) {
            $value = $where['value'] ?? implode('_', $where['values']) ?? '';

            return "{$carry}-{$where['column']}_{$value}";
        });
        $key .= '-' . implode('-', collect($this->eagerLoad)->keys()->toArray());

        return $this->cache([$tag])
            ->rememberForever($key, function () use ($columns) {
                return parent::get($columns);
            });
    }
}
