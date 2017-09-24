<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UncachedProfile extends CachedModel
{
    protected $fillable = [
        'first_name',
        'last_name',
    ];
    protected $table = 'profiles';

    public function author() : BelongsTo
    {
        return $this->belongsTo(UnachedAuthor::class);
    }
}
