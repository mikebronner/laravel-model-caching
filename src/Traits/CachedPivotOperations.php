<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use GeneaLabs\LaravelPivotEvents\Traits\FiresPivotEventsTrait;

trait CachedPivotOperations
{
    use FiresPivotEventsTrait {
        FiresPivotEventsTrait::sync as traitSync;
        FiresPivotEventsTrait::attach as traitAttach;
        FiresPivotEventsTrait::detach as traitDetach;
        FiresPivotEventsTrait::updateExistingPivot as traitUpdateExistingPivot;
    }

    protected $isSyncing = false;

    protected function flushCacheForPivotOperation(): void
    {
        if (method_exists($this->parent, 'flushCache')) {
            $this->parent->flushCache();
        }

        $relatedModel = $this->getRelated();

        if (method_exists($relatedModel, 'flushCache')) {
            $relatedModel->flushCache();
        }
    }

    public function sync($ids, $detaching = true)
    {
        $wasCachable = $this->isCachable;
        $this->isCachable = false;
        $this->isSyncing = true;

        try {
            $result = $this->traitSync($ids, $detaching);
        } finally {
            $this->isSyncing = false;
            $this->isCachable = $wasCachable;
        }

        $this->flushCacheForPivotOperation();

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
            $this->flushCacheForPivotOperation();
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
            $this->flushCacheForPivotOperation();
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
            $this->flushCacheForPivotOperation();
        }

        return $result;
    }
}
