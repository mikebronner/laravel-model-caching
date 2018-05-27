<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UncachedBook extends Model
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

    public function publisher() : BelongsTo
    {
        return $this->belongsTo(UncachedPublisher::class);
    }

    public function stores() : BelongsToMany
    {
        return $this->belongsToMany(UncachedStore::class, "book_store", "book_id", "store_id");
    }
}
