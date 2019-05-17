<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use Cachable;

    public function __construct($attributes = [])
    {
        config(['laravel-model-caching.cache-prefix' => 'test-prefix']);

        parent::__construct($attributes);
    }
}
