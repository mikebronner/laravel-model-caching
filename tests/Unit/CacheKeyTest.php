<?php namespace GeneaLabs\LaravelModelCaching\Tests\Unit;

use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\UnitTestCase;
use ReflectionMethod;

class CacheKeyTest extends UnitTestCase
{
    public function testKeyIsSHA1()
    {
        $makeCacheKey = new ReflectionMethod(
            CachedBuilder::class,
            'makeCacheKey'
        );
        $makeCacheKey->setAccessible(true);

        $builder = (new Author)->startsWithA();
        $key = $makeCacheKey->invoke($builder);

        $this->assertTrue(strlen($key) === 40 && ctype_xdigit($key));
    }
}
