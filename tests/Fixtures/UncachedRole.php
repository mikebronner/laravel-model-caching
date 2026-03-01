<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class UncachedRole extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
    ];
}
