<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Database\Eloquent\Builder;

class MagicMethodProxyTest extends IntegrationTestCase
{
    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(Builder::class);
        $macros = $reflection->getStaticPropertyValue('macros');
        unset($macros['type'], $macros['ofType'], $macros['customFilter']);
        $reflection->setStaticPropertyValue('macros', $macros);

        parent::tearDown();
    }

    public function testGlobalMacroProxyDoesNotThrowBadMethodCallException(): void
    {
        Builder::macro('type', function (string $type) {
            /** @var Builder $this */
            return $this->where('name', 'like', "%{$type}%");
        });

        factory(Author::class, 3)->create(['name' => 'ZZZFICTION-UNIQUE-TEST Author']);
        factory(Author::class, 2)->create(['name' => 'ZZZOTHER-UNIQUE-TEST Author']);

        $fictionAuthors = Author::type('ZZZFICTION-UNIQUE-TEST')->get();

        $this->assertNotEmpty($fictionAuthors);
        $this->assertCount(3, $fictionAuthors);
    }

    public function testGlobalMacroProducesDistinctCacheKeys(): void
    {
        Builder::macro('type', function (string $type) {
            /** @var Builder $this */
            return $this->where('name', 'like', "%{$type}%");
        });

        factory(Author::class)->create(['name' => 'ZZZFICTION-UNIQUE Author']);
        factory(Author::class)->create(['name' => 'ZZZNONFICTION-UNIQUE Author']);

        $fiction = Author::type('ZZZFICTION-UNIQUE')->get();
        $nonFiction = Author::type('ZZZNONFICTION-UNIQUE')->get();

        $this->assertCount(1, $fiction);
        $this->assertCount(1, $nonFiction);
        $this->assertNotEquals($fiction->first()->id, $nonFiction->first()->id);
    }

    public function testTwoDifferentGlobalMacrosProduceDistinctCacheKeys(): void
    {
        Builder::macro('ofType', function (string $type) {
            /** @var Builder $this */
            return $this->where('name', 'like', "%{$type}%");
        });

        Builder::macro('customFilter', function (string $value) {
            /** @var Builder $this */
            return $this->where('email', 'like', "%{$value}%");
        });

        factory(Author::class)->create(['name' => 'ZZZSCIENCE-UNIQUE Author', 'email' => 'zzzsci-unique@example.com']);
        factory(Author::class)->create(['name' => 'ZZZHISTORY-UNIQUE Author', 'email' => 'zzzhistory-unique@example.com']);

        $byName = Author::ofType('ZZZSCIENCE-UNIQUE')->get();
        $byEmail = Author::customFilter('zzzhistory-unique')->get();

        $this->assertCount(1, $byName);
        $this->assertCount(1, $byEmail);
        $this->assertNotEquals($byName->first()->id, $byEmail->first()->id);
    }

    public function testGlobalMacroResultsAreCached(): void
    {
        Builder::macro('type', function (string $type) {
            /** @var Builder $this */
            return $this->where('name', 'like', "%{$type}%");
        });

        factory(Author::class)->create(['name' => 'ZZZCACHED-UNIQUE Author']);
        factory(Author::class)->create(['name' => 'ZZZCACHED-UNIQUE Author 2']);

        $first = Author::type('ZZZCACHED-UNIQUE')->get();
        $second = Author::type('ZZZCACHED-UNIQUE')->get();

        $this->assertCount(2, $first);
        $this->assertCount(2, $second, 'Repeated call with same args should return identical cached results.');
        $this->assertEquals(
            $first->pluck('id')->sort()->values()->toArray(),
            $second->pluck('id')->sort()->values()->toArray(),
            'Both calls should return the same records (from cache).'
        );
    }

    public function testExistingBuilderMethodsStillWorkWithCachedBuilder(): void
    {
        factory(Author::class)->create(['name' => 'Alice']);
        factory(Author::class)->create(['name' => 'Bob']);

        $result = Author::where('name', 'Alice')->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Alice', $result->first()->name);
    }

    public function testLocalScopeStillWorksThroughCachedBuilder(): void
    {
        factory(Author::class)->create(['name' => 'Alpha Author']);
        factory(Author::class)->create(['name' => 'Beta Author']);

        $alphas = Author::startsWithA()->get();
        $uncachedAlphas = (new UncachedAuthor)->startsWithA()->get();

        $this->assertEquals($uncachedAlphas->count(), $alphas->count());
        $this->assertTrue($alphas->every(fn ($a) => str_starts_with($a->name, 'A')));
    }
}
