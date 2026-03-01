<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\Traits\Buildable;
use GeneaLabs\LaravelModelCaching\Traits\BuilderCaching;
use GeneaLabs\LaravelModelCaching\Traits\Caching;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

class CachedBuilder extends Builder
{
    use Buildable;
    use BuilderCaching;
    use Caching;

    protected ?Builder $innerBuilder = null;
    private static ?ReflectionClass $builderReflection = null;

    public function setInnerBuilder(Builder $builder): static
    {
        $this->innerBuilder = $builder;

        return $this;
    }

    public function getInnerBuilder(): ?Builder
    {
        return $this->innerBuilder;
    }

    public function setModel(Model $model)
    {
        $this->innerBuilder?->setModel($model);

        return parent::setModel($model);
    }

    /**
     * Synchronise builder state from the outer CachedBuilder to the inner
     * (custom) builder before delegating a terminal operation.
     *
     * Note: wheres, orders, bindings, and other query-level state are NOT
     * synced here because both builders share the same underlying $query
     * (Illuminate\Database\Query\Builder) instance — it was passed to both
     * constructors. Only Eloquent-level state (eager loads, scopes, etc.)
     * needs explicit propagation.
     */
    protected function syncStateToInner(): void
    {
        if (! $this->innerBuilder) {
            return;
        }

        $this->innerBuilder->setEagerLoads($this->getEagerLoads());
        $this->innerBuilder->pendingAttributes = $this->pendingAttributes;

        if (! self::$builderReflection) {
            self::$builderReflection = new ReflectionClass(Builder::class);
        }

        // WARNING: These properties are internal to Illuminate\Database\Eloquent\Builder
        // and are not part of Laravel's public API. Verified against Laravel 10.x–12.x.
        // If Laravel renames or removes them, this will break. There are no public
        // accessors for `scopes` or `afterQueryCallbacks` as of Laravel 12.
        // `getRemovedScopes()` exists but returns values only (no setter).
        foreach (['scopes', 'removedScopes', 'afterQueryCallbacks'] as $prop) {
            $p = self::$builderReflection->getProperty($prop);
            $p->setValue($this->innerBuilder, $p->getValue($this));
        }
    }

    public function __clone()
    {
        if ($this->innerBuilder) {
            $this->innerBuilder = clone $this->innerBuilder;
        }
    }
}
