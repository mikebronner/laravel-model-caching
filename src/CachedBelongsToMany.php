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
    use FiresPivotEventsTrait {
        FiresPivotEventsTrait::sync as traitSync;
        FiresPivotEventsTrait::attach as traitAttach;
        FiresPivotEventsTrait::detach as traitDetach;
        FiresPivotEventsTrait::updateExistingPivot as traitUpdateExistingPivot;
    }

    protected $isSyncing = false;

    public function sync($ids, $detaching = true)
    {
        $this->isCachable = false;
        $this->isSyncing = true;

        try {
            $result = $this->traitSync($ids, $detaching);
        } finally {
            $this->isSyncing = false;
            $this->isCachable = true;
        }

        $this->flushCache();

        return $result;
    }

    public function attach($ids, array $attributes = [], $touch = true)
    {
        $wasCachable = $this->isCachable;
        $this->isCachable = false;

        try {
            $result = $this->traitAttach($ids, $attributes, $touch);
        } finally {
            $this->isCachable = $wasCachable;
        }

        if (! $this->isSyncing) {
            $this->flushCache();
        }

        return $result;
    }

    public function detach($ids = null, $touch = true)
    {
        $wasCachable = $this->isCachable;
        $this->isCachable = false;

        try {
            $result = $this->traitDetach($ids, $touch);
        } finally {
            $this->isCachable = $wasCachable;
        }

        if (! $this->isSyncing) {
            $this->flushCache();
        }

        return $result;
    }

    public function updateExistingPivot($id, array $attributes, $touch = true)
    {
        $wasCachable = $this->isCachable;
        $this->isCachable = false;

        try {
            $result = $this->traitUpdateExistingPivot($id, $attributes, $touch);
        } finally {
            $this->isCachable = $wasCachable;
        }

        if (! $this->isSyncing) {
            $this->flushCache();
        }

        return $result;
    }
}
