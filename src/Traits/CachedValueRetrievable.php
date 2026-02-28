<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use Illuminate\Support\Facades\Log;

trait CachedValueRetrievable
{
    public function cachedValue(array $arguments, string $cacheKey)
    {
        $method = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        $cacheTags = $this->makeCacheTags();
        $hashedCacheKey = sha1($cacheKey);

        try {
            $result = $this->retrieveCachedValue(
                $arguments,
                $cacheKey,
                $cacheTags,
                $hashedCacheKey,
                $method
            );

            return $this->preventHashCollision(
                $result,
                $arguments,
                $cacheKey,
                $cacheTags,
                $hashedCacheKey,
                $method
            );
        } catch (\Exception $exception) {
            if (! $this->shouldFallbackToDatabase()) {
                throw $exception;
            }

            Log::warning("laravel-model-caching: cache read failed, falling back to database — {$exception->getMessage()}");

            return parent::{$method}(...$arguments);
        }
    }

    protected function preventHashCollision(
        array $result,
        array $arguments,
        string $cacheKey,
        array $cacheTags,
        string $hashedCacheKey,
        string $method
    ) {
        if ($result["key"] === $cacheKey) {
            return $result["value"];
        }

        $this->cache()
            ->tags($cacheTags)
            ->forget($hashedCacheKey);

        return $this->retrieveCachedValue(
            $arguments,
            $cacheKey,
            $cacheTags,
            $hashedCacheKey,
            $method
        );
    }

    protected function retrieveCachedValue(
        array $arguments,
        string $cacheKey,
        array $cacheTags,
        string $hashedCacheKey,
        string $method
    ) {
        if (property_exists($this, "model")) {
            $this->checkCooldownAndRemoveIfExpired($this->model);
        }

        if (method_exists($this, "getModel")) {
            $this->checkCooldownAndRemoveIfExpired($this->getModel());
        }

        $cache = $this->cache($cacheTags);
        $cachedResult = $cache->get($hashedCacheKey);

        if ($cachedResult !== null) {
            $this->fireRetrievedEvents($cachedResult["value"] ?? null);

            return $cachedResult;
        }

        $result = [
            "key" => $cacheKey,
            "value" => parent::{$method}(...$arguments),
        ];

        $cache->forever($hashedCacheKey, $result);

        return $result;
    }

    protected function fireRetrievedEvents($value): void
    {
        if ($value instanceof \Illuminate\Database\Eloquent\Model) {
            $this->fireRetrievedEventOnModel($value);

            return;
        }

        $models = null;

        if ($value instanceof \Illuminate\Database\Eloquent\Collection) {
            $models = $value;
        } elseif ($value instanceof \Illuminate\Contracts\Pagination\Paginator) {
            $models = $value->getCollection();
        } elseif ($value instanceof \Illuminate\Support\Collection) {
            $models = $value->filter(function ($item) {
                return $item instanceof \Illuminate\Database\Eloquent\Model;
            });
        }

        if ($models) {
            $models->each(function ($model) {
                if ($model instanceof \Illuminate\Database\Eloquent\Model) {
                    $this->fireRetrievedEventOnModel($model);
                }
            });
        }
    }

    protected function fireRetrievedEventOnModel(\Illuminate\Database\Eloquent\Model $model): void
    {
        $dispatcher = $model::getEventDispatcher();

        if ($dispatcher) {
            $dispatcher->dispatch(
                "eloquent.retrieved: " . get_class($model),
                $model
            );
        }
    }
}
