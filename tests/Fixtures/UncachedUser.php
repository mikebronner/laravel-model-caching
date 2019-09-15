<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UncachedUser extends Model
{
    protected $fillable = [
        "name",
        "supplier_id",
    ];
    protected $table = "users";

    public function supplier() : BelongsTo
    {
        return $this->belongsTo(UncachedSupplier::class, "supplier_id");
    }
}
