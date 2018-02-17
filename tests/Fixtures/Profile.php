<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    use Cachable;

    protected $fillable = [
        'first_name',
        'last_name',
    ];

    public function author() : BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}
