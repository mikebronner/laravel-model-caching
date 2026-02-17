<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An Author model that declares a custom Eloquent builder via the static
 * $builder property.  The custom builder does NOT extend CachedBuilder,
 * so when caching is enabled the package must fall back to CachedBuilder;
 * when caching is disabled it should return the custom builder.
 */
class AuthorWithCustomBuilder extends Model
{
    use Cachable;
    use SoftDeletes;

    protected $table = 'authors';

    protected static string $builder = AuthorQueryBuilder::class;

    protected $fillable = [
        'name',
        'email',
        'is_famous',
    ];
}
