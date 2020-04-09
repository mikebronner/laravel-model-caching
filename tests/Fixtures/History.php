<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class History extends Model
{
    use Cachable;

    protected $fillable = [
        "name",
        "user_id",
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
