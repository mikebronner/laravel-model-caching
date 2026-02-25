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

    /**
     * Sync the intermediate tables with a list of IDs or collection of models.
     *
     * Overrides FiresPivotEventsTrait::sync() to avoid using withoutEvents(),
     * which suppresses ALL model events globally — including events on custom
     * pivot models, preventing their observers from firing.
     *
     * @param  mixed  $ids
     * @param  bool  $detaching
     * @return array
     */
    public function sync($ids, $detaching = true)
    {
        if (false === $this->parent->fireModelEvent('pivotSyncing', true, $this->getRelationName())) {
            return [];
        }

        // Call the base BelongsToMany::sync() directly instead of wrapping in
        // withoutEvents(). This allows custom pivot model events (and their
        // observers) to fire correctly during attach/detach operations.
        $parentResult = parent::sync($ids, $detaching);

        $this->parent->fireModelEvent('pivotSynced', false, $this->getRelationName(), $parentResult);

        return $parentResult;
    }
}
