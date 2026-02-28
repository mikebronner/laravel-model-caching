<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Tests\Database\Factories\PostFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Post extends Model
{
    use Cachable;
    use HasFactory;

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }

    protected $fillable = [
        "title",
        "body",
    ];

    public function comments() : MorphMany
    {
        return $this->morphMany(Comment::class, "commentable");
    }

    public function tags() : MorphToMany
    {
        return $this->morphToMany(Tag::class, "taggable");
    }
}
