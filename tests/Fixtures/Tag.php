<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Tests\Database\Factories\TagFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use Cachable;
    use HasFactory;

    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }

    protected $fillable = [
        "name",
    ];

    public function posts() : MorphToMany
    {
        return $this->morphedByMany(Post::class, "taggable");
    }
}
