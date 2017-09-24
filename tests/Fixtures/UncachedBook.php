<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UncachedBook extends CachedModel
{
    protected $dates = [
        'published_at',
    ];
    protected $fillable = [
        'description',
        'published_at',
        'title',
    ];
    protected $table = 'books';

    public function author() : BelongsTo
    {
        return $this->belongsTo(UncachedAuthor::class);
    }

    public function stores() : BelongsToMany
    {
        return $this->belongsToMany(UncachedStore::class);
    }
}
