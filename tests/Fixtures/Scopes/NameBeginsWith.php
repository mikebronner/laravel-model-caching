<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class NameBeginsWith implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('name', 'LIKE', "A%");
    }
}
