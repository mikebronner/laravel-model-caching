<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class UncachedUser extends Model
{
    protected $fillable = [
        "name",
        "supplier_id",
    ];
    protected $table = "users";

    public function roles() : BelongsToMany
    {
        return $this->belongsToMany(UncachedRole::class, 'role_user', 'user_id', 'role_id');
    }

    public function supplier() : BelongsTo
    {
        return $this->belongsTo(UncachedSupplier::class, "supplier_id");
    }

    public function image() : MorphOne
    {
        return $this->morphOne(UncachedImage::class, "imagable");
    }
}
