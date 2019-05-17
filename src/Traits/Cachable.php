<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;

trait Cachable
{
    use Caching;
    use ModelCaching;
    use PivotEventTrait {
        ModelCaching::newBelongsToMany insteadof PivotEventTrait;
    }
}
