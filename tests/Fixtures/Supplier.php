<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use Cachable;

    protected $fillable = [
        'name',
    ];

    public function user() : HasOne
    {
        return $this->hasOne(User::class);
    }

    public function history() : HasOneThrough
    {
        return $this->hasOneThrough(History::class, User::class);
    }
}
