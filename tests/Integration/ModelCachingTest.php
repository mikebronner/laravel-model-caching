<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration;

use GeneaLabs\LaravelModelCaching\ModelCaching;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\NestedSetAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Kalnoy\Nestedset\QueryBuilder;

class ModelCachingTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        ModelCaching::useEloquentBuilder(QueryBuilder::class);

        parent::setUp();
    }
    /** @group test */
    public function testClosureRunsWithCacheDisabled()
    {
        $authors = (new NestedSetAuthor)
            ->get();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->with('books', 'profile')
            ->get();
dd($authors->first()->prependNode($authors->last()));
        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
        $this->assertTrue(method_exists($authors->first(), "prependNode"));
    }
}
