<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An Author model that declares a custom builder that extends CachedBuilder.
 * When caching is enabled, the package should detect that the custom builder
 * already inherits caching support and return it directly so custom query
 * methods are preserved alongside full caching behaviour.
 */
class AuthorWithCachedCustomBuilder extends Model
{
    use Cachable;
    use SoftDeletes;

    protected $table = 'authors';

    protected static string $builder = AuthorCachedQueryBuilder::class;

    protected $fillable = [
        'name',
        'email',
        'is_famous',
    ];
}
