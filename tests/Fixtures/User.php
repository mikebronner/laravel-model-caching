<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use Cachable;

    protected $fillable = [
        "name",
        "supplier_id",
    ];

    public function supplier() : BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
