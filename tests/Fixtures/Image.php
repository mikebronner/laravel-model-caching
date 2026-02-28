<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Tests\Database\Factories\ImageFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    use Cachable;
    use HasFactory;

    protected static function newFactory(): ImageFactory
    {
        return ImageFactory::new();
    }

    protected $fillable = [
        'path',
    ];

    public function imagable(): MorphTo
    {
        return $this->morphTo();
    }
}
