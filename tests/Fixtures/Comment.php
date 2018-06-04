<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use Cachable;

    protected $fillable = [
        'description',
        'subject',
    ];

    public function commentable() : MorphTo
    {
        return $this->morphTo();
    }
}
