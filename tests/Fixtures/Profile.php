<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Tests\Database\Factories\ProfileFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    use Cachable;
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
    ];

    protected static function newFactory(): ProfileFactory
    {
        return ProfileFactory::new();
    }

    public function author() : BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}
