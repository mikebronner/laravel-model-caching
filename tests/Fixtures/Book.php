<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends CachedModel
{
    protected $casts = [
        'price' => 'float',
    ];
    protected $dates = [
        'published_at',
    ];
    protected $fillable = [
        'description',
        'published_at',
        'title',
        'price',
    ];

    public function author() : BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function publisher() : BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function stores() : BelongsToMany
    {
        return $this->belongsToMany(Store::class);
    }
}
