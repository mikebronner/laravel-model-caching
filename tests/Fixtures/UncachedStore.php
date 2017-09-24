<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UncachedStore extends CachedModel
{
    protected $fillable = [
        'address',
        'name',
    ];
    protected $table = 'stores';

    public function books() : BelongsToMany
    {
        return $this->belongsToMany(UncachedBook::class);
    }
}
