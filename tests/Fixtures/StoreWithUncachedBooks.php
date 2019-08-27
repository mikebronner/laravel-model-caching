<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StoreWithUncachedBooks extends Model
{
    use Cachable;

    protected $fillable = [
        'address',
        'name',
    ];
    protected $table = "stores";

    public function books() : BelongsToMany
    {
        return $this->belongsToMany(UncachedBook::class, "book_store", "store_id", "book_id");
    }
}
