<?php

namespace GeneaLabs\LaravelModelCaching;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Scope;

/**
 * Generates cache keys from a cloned builder instance,
 * ensuring the original builder is never mutated during
 * key generation.
 */
class CacheKeyGenerator
{
    /**
     * Generate a cache key from a cloned builder instance.
     *
     * The original builder is never mutated — all scope application
     * and key computation happens on a separate clone.
     */
    public static function generate(
        EloquentBuilder $builder,
        array $columns = ['*'],
        $idColumn = null,
        string $keyDifferentiator = ''
    ): string {
        $clone = static::cloneBuilder($builder);

        $eagerLoad = $clone->getEagerLoads();
        $model = $clone->getModel();

        $metadata = $clone->getCacheKeyMetadata();

        $query = $clone->getQuery();

        return (new CacheKey(
            $eagerLoad,
            $model,
            $query,
            $metadata['macroKey'],
            $metadata['withoutGlobalScopes'],
            $metadata['withoutAllGlobalScopes']
        ))->make($columns, $idColumn, $keyDifferentiator);
    }

    /**
     * Create a deep clone of the builder with scopes applied,
     * without mutating the original builder instance.
     */
    protected static function cloneBuilder(EloquentBuilder $builder): EloquentBuilder
    {
        $clone = clone $builder;
        $clone->setQuery(clone $builder->getQuery());

        static::applyScopesOnClone($clone);

        return $clone;
    }

    /**
     * Apply global scopes to the clone using a bound closure
     * to access protected builder internals.
     */
    protected static function applyScopesOnClone(EloquentBuilder $clone): void
    {
        if (method_exists($clone, 'setScopesAreApplied')) {
            $metadata = $clone->getCacheKeyMetadata();

            if ($metadata['scopesAreApplied'] || $metadata['withoutAllGlobalScopes']) {
                return;
            }

            $clone->setScopesAreApplied(false);
        }

        // Bind to the clone's actual class to access protected $scopes and callScope()
        $applier = Closure::bind(function () {
            $scopes = $this->scopes ?? [];
            $removedScopes = $this->removedScopes ?? [];

            foreach ($scopes as $identifier => $scope) {
                if (isset($removedScopes[$identifier])) {
                    continue;
                }

                // Skip the CachingScope — it doesn't modify queries
                if ($scope instanceof CachingScope) {
                    continue;
                }

                $this->callScope(function () use ($scope) {
                    if ($scope instanceof Closure) {
                        $scope($this);
                    }

                    if ($scope instanceof Scope) {
                        $scope->apply($this, $this->getModel());
                    }
                });
            }
        }, $clone, get_class($clone));

        $applier();

        if (method_exists($clone, 'setScopesAreApplied')) {
            $clone->setScopesAreApplied(true);
        }
    }
}
