<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UncachedAuthor extends Model
{
    protected $fillable = [
        'name',
        'email',
    ];
    protected $table = 'authors';

    public function books() : HasMany
    {
        return $this->hasMany(UncachedBook::class, 'author_id', 'id');
    }

    public function profile() : HasOne
    {
        return $this->hasOne(UncachedProfile::class, 'author_id', 'id');
    }
}
