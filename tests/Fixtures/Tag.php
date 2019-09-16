<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use Cachable;

    protected $fillable = [
        "name",
    ];

    public function posts() : MorphToMany
    {
        return $this->morphedByMany(Post::class, "taggable");
    }
}
