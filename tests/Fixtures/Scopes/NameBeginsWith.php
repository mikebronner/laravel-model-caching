<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Str;

class NameBeginsWith implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $letter = (new Str)->substr(auth()->user()->name, 0, 1);
        $builder->where('name', 'LIKE', "{$letter}%");
    }
}
