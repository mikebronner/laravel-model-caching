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
