<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;

trait Cachable
{
    use Caching,
        ModelCaching,
        PivotEventTrait {
            ModelCaching::newBelongsToMany insteadof PivotEventTrait;
        }
}
