<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Store extends CachedModel
{
    protected $fillable = [
        'address',
        'name',
    ];

    public function books() : BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }
}
