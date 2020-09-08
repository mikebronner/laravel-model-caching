<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UncachedHistory extends Model
{
    protected $fillable = [
        "name",
        "user_id",
    ];
    protected $table = "histories";

    public function user() : BelongsTo
    {
        return $this->belongsTo(UncachedUser::class, "user_id");
    }
}
