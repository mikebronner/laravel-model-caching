<?php namespace GeneaLabs\LaravelModelCaching\Traits;

trait CachePrefixing
{
    protected function getCachePrefix() : string
    {
        return "genealabs:laravel-model-caching:"
            . $this->getDatabaseConnectionName() . ":"
            . (config("laravel-model-caching.cache-prefix")
                ? config("laravel-model-caching.cache-prefix", "") . ":"
                : "");
    }

    protected function getDatabaseConnectionName() : string
    {
        return $this->query->connection->getName();
    }
}
