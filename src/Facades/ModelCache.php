<?php

namespace GeneaLabs\LaravelModelCaching\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void invalidate(string|array $modelClasses)
 * @method static mixed runDisabled(callable $closure)
 *
 * @see \GeneaLabs\LaravelModelCaching\Helper
 */
class ModelCache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'model-cache';
    }
}
