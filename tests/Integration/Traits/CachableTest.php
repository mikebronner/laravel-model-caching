<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\Traits;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\PrefixedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Database\Eloquent\Collection;

class CachableTest extends IntegrationTestCase
{
    public function test_specifying_alternate_cache_driver()
    {
        $configCacheStores = config('cache.stores');
        $configCacheStores['customCache'] = ['driver' => 'array'];
        // TODO: make sure the alternate cache is actually loaded
        config(['cache.stores' => $configCacheStores]);
        config(['laravel-model-caching.store' => 'customCache']);
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor"];

        $authors = (new Author)
            ->all();
        $defaultcacheResults = app('cache')
            ->tags($tags)
            ->get($key)['value']
            ?? null;
        $customCacheResults = app('cache')
            ->store('customCache')
            ->tags($tags)
            ->get($key)['value']
            ?? null;
        $liveResults = (new UncachedAuthor)
            ->all();

        $this->assertEquals($customCacheResults, $authors);
        $this->assertNull($defaultcacheResults);
        $this->assertEmpty($liveResults->diffAssoc($customCacheResults));
    }

    public function test_set_cache_prefix_attribute()
    {
        (new PrefixedAuthor)->get();

        $results = $this->
            cache()
                ->tags([
                    "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:model-prefix:genealabslaravelmodelcachingtestsfixturesprefixedauthor",
                ])
                ->get(sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:model-prefix:authors:genealabslaravelmodelcachingtestsfixturesprefixedauthor-authors.deleted_at_null"))['value'];

        $this->assertNotNull($results);
    }

    public function test_all_returns_collection()
    {
        (new Author)->truncate();
        Author::factory()->count(1)->create();
        $authors = (new Author)->all();

        $cachedResults = $this
            ->cache()
            ->tags([
                "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            ])
            ->get(sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null"))['value'];
        $liveResults = (new UncachedAuthor)->all();

        $this->assertInstanceOf(Collection::class, $authors);
        $this->assertInstanceOf(Collection::class, $cachedResults);
        $this->assertInstanceOf(Collection::class, $liveResults);
    }

    public function tests_cache_flag_disables_caching()
    {
        config(['laravel-model-caching.enabled' => false]);

        $authors = (new Author)->get();
        $cachedAuthors = $this
            ->cache()
            ->tags([
                "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            ])
            ->get(sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null"));

        config(['laravel-model-caching.enabled' => true]);

        $this->assertNull($cachedAuthors);
        $this->assertNotEmpty($authors);
        $this->assertCount(10, $authors);
    }
}
