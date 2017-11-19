<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Author extends CachedModel
{
    protected $fillable = [
        'name',
        'email',
    ];

    public function books() : HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function profile() : HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function scopeStartsWithA(Builder $query) : Builder
    {
        return $query->where('name', 'LIKE', 'A%');
    }
}
