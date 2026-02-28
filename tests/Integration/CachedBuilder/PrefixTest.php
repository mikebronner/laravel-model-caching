<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\PrefixedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class PrefixTest extends IntegrationTestCase
{
    public function test_cache_prefix_is_added_for_prefixed_model()
    {
        $prefixKey = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:model-prefix:authors:genealabslaravelmodelcachingtestsfixturesprefixedauthor-authors.deleted_at_null-first");
        $prefixTags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:model-prefix:genealabslaravelmodelcachingtestsfixturesprefixedauthor",
        ];
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null-first");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $prefixAuthor = (new PrefixedAuthor)
            ->first();
        $author = (new Author)
            ->first();
        $prefixCachedResults = $this
            ->cache()
            ->tags($prefixTags)
            ->get($prefixKey)['value'];
        $nonPrefixCachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];

        $this->assertEquals($prefixCachedResults, $prefixAuthor);
        $this->assertEquals($nonPrefixCachedResults, $author);
        $this->assertNotNull($author);
    }
}
