<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;

/**
 * A trait that defines newEloquentBuilder, simulating packages like
 * kalnoy/nestedset (NodeTrait) that override the Eloquent builder.
 */
trait ConflictingBuilderTrait
{
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }
}
