<?php

namespace GeneaLabs\LaravelModelCaching;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

class QueryOrModelCaller
{
    /**
     * @var CachedBuilder
     */
    protected $query;
    /**
     * @var Model
     */
    protected $model;
    /**
     * @var bool
     */
    protected $disableInConfig;

    /**
     * QueryOrModelCaller constructor.
     *
     * @param EloquentBuilder $query
     * @param Model           $model
     * @param bool            $disableInConfig
     */
    public function __construct(EloquentBuilder $query, Model $model, bool $disableInConfig)
    {
        $this->query = $query;
        $this->model = $model;
        $this->disableInConfig = $disableInConfig;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if ($name == 'all')
            $result = call_user_func_array([$this->model, 'all'], $arguments);
        else
            $result = call_user_func_array([$this->query, $name], $arguments);

        config()->set('laravel-model-caching.disabled', $this->disableInConfig);

        return $result;
    }
}
