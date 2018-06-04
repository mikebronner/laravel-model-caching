<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UncachedComment extends Model
{
    protected $fillable = [
        'description',
        'subject',
    ];
    protected $table = "comments";

    public function commentable() : MorphTo
    {
        return $this->morphTo();
    }
}
