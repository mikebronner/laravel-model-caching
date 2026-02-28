<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\CachedBuilder;

/**
 * A custom query builder that extends CachedBuilder, giving models both
 * custom query methods AND full caching support.
 */
class AuthorCachedQueryBuilder extends CachedBuilder
{
    public function famous(): static
    {
        return $this->where('is_famous', true);
    }
}
