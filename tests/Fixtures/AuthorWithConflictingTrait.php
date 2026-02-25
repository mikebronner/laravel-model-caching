<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model that uses Cachable alongside another trait defining newEloquentBuilder.
 * Demonstrates the recommended resolution pattern for trait collisions.
 */
class AuthorWithConflictingTrait extends Model
{
    use Cachable, ConflictingBuilderTrait {
        Cachable::newEloquentBuilder insteadof ConflictingBuilderTrait;
    }

    protected $table = 'authors';

    protected $fillable = [
        'name',
        'email',
    ];

    public function books() : HasMany
    {
        return $this->hasMany(Book::class, 'author_id');
    }
}
