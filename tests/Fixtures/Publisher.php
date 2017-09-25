<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publisher extends CachedModel
{
    protected $fillable = [
        'name',
    ];

    public function books() : HasMany
    {
        return $this->hasMany(Book::class);
    }
}
