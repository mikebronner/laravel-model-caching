<?php namespace GeneaLabs\LaravelModelCaching\Traits;

trait CachePrefixing
{
    protected function getCachePrefix() : string
    {
        return "genealabs:laravel-model-caching:"
            . $this->getConnectionName() . ":"
            . $this->getDatabaseName() . ":"
            . (config("laravel-model-caching.cache-prefix")
                ? config("laravel-model-caching.cache-prefix", "") . ":"
                : "");
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
