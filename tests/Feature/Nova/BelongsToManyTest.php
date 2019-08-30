<?php namespace GeneaLabs\LaravelModelCaching\Tests\Feature\Nova;

use GeneaLabs\LaravelModelCaching\Tests\FeatureTestCase;

class BelongsToManyTest extends FeatureTestCase
{
    /** @group test */
    public function testCacheCanBeDisabledOnModel()
    {
        dd();
        $result = $this->visit("/nova");

        dd($result);
    }
}
