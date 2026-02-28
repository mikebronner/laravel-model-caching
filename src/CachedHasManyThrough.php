<?php

namespace GeneaLabs\LaravelModelCaching;

use GeneaLabs\LaravelModelCaching\Traits\CachesOneOrManyThrough;
use GeneaLabs\LaravelModelCaching\Traits\Caching;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CachedHasManyThrough extends HasManyThrough
{
    use CachesOneOrManyThrough {
        CachesOneOrManyThrough::makeCacheKey insteadof Caching;
        CachesOneOrManyThrough::makeCacheTags insteadof Caching;
    }
    use Caching;
}
