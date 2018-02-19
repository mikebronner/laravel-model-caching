<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BaseModel extends Model
{
    use Cachable;

    public function __construct($attributes = [])
    {
        config(['genealabs:laravel-model-caching' => 'test-prefix']);

        parent::__construct($attributes);
    }
}
