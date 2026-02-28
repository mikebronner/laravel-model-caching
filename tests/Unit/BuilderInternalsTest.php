<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Unit;

use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Version-gated assertions that verify the protected Eloquent Builder
 * internals accessed by CacheKeyGenerator::applyScopesOnClone() still
 * exist. If Laravel changes these internals, these tests will fail
 * and alert us to update the Closure::bind logic.
 */
class BuilderInternalsTest extends TestCase
{
    public function testBuilderHasScopesProperty(): void
    {
        $reflection = new ReflectionClass(Builder::class);

        $this->assertTrue(
            $reflection->hasProperty('scopes'),
            'Eloquent Builder no longer has a protected $scopes property — CacheKeyGenerator::applyScopesOnClone() must be updated.'
        );
    }

    public function testBuilderHasWithoutGlobalScopesProperty(): void
    {
        $reflection = new ReflectionClass(Builder::class);

        $this->assertTrue(
            $reflection->hasProperty('withoutGlobalScopes'),
            'Eloquent Builder no longer has a protected $withoutGlobalScopes property — CacheKeyGenerator::applyScopesOnClone() must be updated.'
        );
    }

    public function testBuilderHasCallScopeMethod(): void
    {
        $reflection = new ReflectionClass(Builder::class);

        $this->assertTrue(
            $reflection->hasMethod('callScope'),
            'Eloquent Builder no longer has a callScope() method — CacheKeyGenerator::applyScopesOnClone() must be updated.'
        );
    }
}
