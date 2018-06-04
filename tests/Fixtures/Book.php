<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends Model
{
    use Cachable;

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

    public function comments() : MorphMany
    {
        return $this->morphMany(Comment::class, "commentable");
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
