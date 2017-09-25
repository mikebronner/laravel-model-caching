<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UncachedPublisher extends CachedModel
{
    protected $fillable = [
        'name',
    ];
    protected $table = 'publishers';

    public function books() : HasMany
    {
        return $this->hasMany(Book::class, 'publisher_id', 'id');
    }
}
