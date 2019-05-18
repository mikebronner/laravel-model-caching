<?php namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelPivotEvents\Traits\FiresPivotEventsTrait;
use GeneaLabs\LaravelModelCaching\Traits\Buildable;
use GeneaLabs\LaravelModelCaching\Traits\BuilderCaching;
use GeneaLabs\LaravelModelCaching\Traits\Caching;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CachedBelongsToMany extends BelongsToMany
{
    use Buildable;
    use BuilderCaching;
    use Caching;
    use FiresPivotEventsTrait;

    public function getRelation($name)
    {
        $relation = parent::getRelation($name);

        if (! $this->isCachable()
            && is_a($relation->getQuery(), self::class)
        ) {
            $relation->getQuery()->disableModelCaching();
        }

        return $relation;
    }
}
