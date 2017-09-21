<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
