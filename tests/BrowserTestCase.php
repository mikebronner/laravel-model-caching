<?php namespace GeneaLabs\LaravelModelCaching\Tests;

use Orchestra\Testbench\Dusk\TestCase as BaseTestCase;

abstract class BrowserTestCase extends BaseTestCase
{
    use CreatesApplication;

    public static function setUpBeforeClass()
    {
        static::serve();
    }

    public static function tearDownAfterClass()
    {
         static::stopServing();
    }
}
