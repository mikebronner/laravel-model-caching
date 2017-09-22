<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends CachedModel
{
    protected $fillable = [
        'first_name',
        'last_name',
    ];

    public function author() : BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}
