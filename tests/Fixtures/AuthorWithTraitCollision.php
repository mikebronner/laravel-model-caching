<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An Author model that combines Cachable with a second trait (FakeNodeTrait)
 * that also defines newEloquentBuilder — the classic AC6 / #535 collision.
 *
 * The collision is resolved by explicitly preferring Cachable's implementation
 * via the `insteadof` keyword; caching is still active because we delegate to
 * newModelCachingEloquentBuilder().
 */
class AuthorWithTraitCollision extends Model
{
    use Cachable, FakeNodeTrait {
        Cachable::newEloquentBuilder insteadof FakeNodeTrait;
    }
    use SoftDeletes;

    protected $table = 'authors';
    protected $fillable = [
        'name',
        'email',
        'is_famous',
    ];
}
