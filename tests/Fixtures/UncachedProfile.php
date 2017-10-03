<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UncachedProfile extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
    ];
    protected $table = 'profiles';

    public function author() : BelongsTo
    {
        return $this->belongsTo(UncachedAuthor::class);
    }
}
