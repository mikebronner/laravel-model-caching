<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model that uses Cachable alongside another trait defining newEloquentBuilder,
 * resolving the collision by defining newEloquentBuilder on the class itself
 * and delegating to the helper method.
 */
class AuthorWithMergedTraits extends Model
{
    use Cachable, ConflictingBuilderTrait {
        Cachable::newEloquentBuilder as newCachableEloquentBuilder;
        ConflictingBuilderTrait::newEloquentBuilder as newConflictingEloquentBuilder;
    }

    protected $table = 'authors';

    protected $fillable = [
        'name',
        'email',
    ];

    public function newEloquentBuilder($query)
    {
        // Delegate to the caching builder via the helper method
        return $this->newModelCachingEloquentBuilder($query);
    }

    public function books() : HasMany
    {
        return $this->hasMany(Book::class, 'author_id');
    }
}
