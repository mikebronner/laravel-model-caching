<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UncachedImage extends Model
{
    protected $fillable = [
        'path',
    ];

    protected $table = 'images';

    public function imagable(): MorphTo
    {
        return $this->morphTo();
    }
}
