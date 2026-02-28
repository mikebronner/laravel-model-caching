<?php

namespace GeneaLabs\LaravelModelCaching;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CachingScope implements Scope
{
    /**
     * Extensions registered on the builder via this scope.
     *
     * @var string[]
     */
    protected $extensions = [
        'DisableModelCaching',
        'FlushCache',
    ];

    public function apply(Builder $builder, Model $model): void
    {
        // No query modification needed — caching is handled
        // by the terminal method interception in CachedBuilder
        // and the macros registered in extend().
    }

    public function extend(Builder $builder): void
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    protected function addDisableModelCaching(Builder $builder): void
    {
        $builder->macro('disableModelCaching', function (Builder $builder) {
            $builder->isCachable = false;

            return $builder;
        });
    }

    protected function addFlushCache(Builder $builder): void
    {
        $builder->macro('flushCache', function (Builder $builder, array $tags = []) {
            $model = $builder->getModel();

            if (method_exists($model, 'flushCache')) {
                $model->flushCache($tags);
            }

            return $builder;
        });
    }


}
