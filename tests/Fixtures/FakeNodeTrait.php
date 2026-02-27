<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

/**
 * A minimal stand-in for third-party traits (e.g. Kalnoy\Nestedset\NodeTrait)
 * that also define `newEloquentBuilder`.  Used to test AC6: no fatal collision
 * when `Cachable` is combined with another such trait.
 */
trait FakeNodeTrait
{
    public function newEloquentBuilder($query)
    {
        return parent::newEloquentBuilder($query);
    }
}
