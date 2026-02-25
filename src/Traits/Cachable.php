<?php namespace GeneaLabs\LaravelModelCaching\Traits;

use GeneaLabs\LaravelPivotEvents\Traits\PivotEventTrait;

trait Cachable
{
    use Caching;
    use ModelCaching;
    use PivotEventTrait {
        ModelCaching::newBelongsToMany insteadof PivotEventTrait;
    }

    /**
     * Initialize the Cachable trait on each new model instance.
     *
     * Called by Eloquent during __construct() → initializeTraits(). This
     * intentionally avoids modifying $fillable, $guarded, $casts, or any
     * other model properties so that dynamic changes made in a model's
     * constructor (before calling parent::__construct) are preserved.
     *
     * @see https://github.com/mikebronner/laravel-model-caching/issues/534
     */
    public function initializeCachable(): void
    {
        // Intentionally empty — the Cachable trait must not interfere with
        // model properties (especially $fillable) that may have been set
        // dynamically in the model's constructor prior to parent::__construct().
    }
}
