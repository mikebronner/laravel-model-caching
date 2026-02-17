<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UncachedRole extends Model
{
    protected $fillable = [
        'name',
    ];
    protected $table = 'roles';

    public function users() : BelongsToMany
    {
        return $this->belongsToMany(UncachedUser::class, 'role_user', 'role_id', 'user_id');
    }
}
