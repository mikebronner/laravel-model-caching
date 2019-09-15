<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    use Cachable;

    protected $fillable = [
        "title",
        "body",
    ];

    public function comments() : MorphMany
    {
        return $this->morphMany(Comment::class, "commentable");
    }
}
