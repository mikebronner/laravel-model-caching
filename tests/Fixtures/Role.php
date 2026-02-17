<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use Cachable;

    protected $fillable = [
        'name',
    ];

    public function users() : BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
