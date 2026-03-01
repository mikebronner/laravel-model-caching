<?php namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\Traits\CachesOneOrManyThrough;
use GeneaLabs\LaravelModelCaching\Traits\Caching;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class CachedHasOneThrough extends HasOneThrough
{
    use Caching;
    use CachesOneOrManyThrough {
        CachesOneOrManyThrough::makeCacheKey insteadof Caching;
        CachesOneOrManyThrough::makeCacheTags insteadof Caching;
    }

    protected function makeCacheTags(): array
    {
        $eagerLoad = $this->eagerLoad ?? [];
        $model = $this->getModel();
        $query = $this->getQuery()->getQuery();

        $tags = (new CacheTags($eagerLoad, $model, $query))->make();

        $throughTags = (new CacheTags([], $this->throughParent, $query))->make();

        foreach ($throughTags as $throughTag) {
            if (! in_array($throughTag, $tags)) {
                $tags[] = $throughTag;
            }
        }

        return $tags;
    }
}
