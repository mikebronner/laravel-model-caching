<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;

/**
 * A custom Eloquent query builder for Author that does NOT extend CachedBuilder.
 * Used to verify the non-cachable path and the cachable fallback in
 * ModelCaching::newEloquentBuilder().
 */
class AuthorQueryBuilder extends Builder
{
    public function famous(): static
    {
        return $this->where('is_famous', true);
    }
}
