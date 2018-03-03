<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PrefixedAuthor extends BaseModel
{
    protected $fillable = [
        'name',
        'email',
    ];
    protected $table = "authors";
}
