<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Tests\Database\Factories\CommentFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use Cachable;
    use HasFactory;

    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }

    protected $fillable = [
        'description',
        'subject',
        'commentable_id',
        'commentable_type',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
