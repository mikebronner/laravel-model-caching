<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Store extends Model
{
    use Cachable;

    protected $fillable = [
        'address',
        'name',
    ];

    public function books() : BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }
}
