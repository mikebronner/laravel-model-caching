<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use Illuminate\Database\Eloquent\Collection;

trait BuilderCaching
{
    public function all($columns = ['*']) : Collection
    {
        if (! $this->isCachable()) {
            $this->model->disableModelCaching();
        }

        return $this->model->get($columns);
    }
}
