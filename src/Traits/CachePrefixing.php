<?php namespace GeneaLabs\LaravelModelCaching\Traits;

trait CachePrefixing
{
    protected function getCachePrefix() : string
    {
        $cachePrefix = config("laravel-model-caching.cache-prefix", "");

        if ($this->model
            && property_exists($this->model, "cachePrefix")
        ) {
            $cachePrefix = $this->model->cachePrefix;
        }

        $cachePrefix = $cachePrefix
            ? "{$cachePrefix}:"
            : "";

        return "genealabs:laravel-model-caching:"
            . $this->getConnectionName() . ":"
            . $this->getDatabaseName() . ":"
            . $cachePrefix;
    }

    protected function getDatabaseName() : string
    {
        return $this->query->getConnection()->getDatabaseName();
    }

    protected function getConnectionName() : string
    {
        return $this->model->getConnection()->getName();
    }
}
