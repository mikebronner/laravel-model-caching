<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class UncachedPost extends Model
{
    protected $fillable = [
        "title",
        "body",
    ];
    protected $table = "posts";

    public function comments() : MorphMany
    {
        return $this->morphMany(UncachedComment::class, "commentable");
    }
}
