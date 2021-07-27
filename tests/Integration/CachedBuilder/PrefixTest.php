<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\PrefixedAuthor;

class PrefixTest extends IntegrationTestCase
{
    public function testCachePrefixIsAddedForPrefixedModel()
    {
        $prefixKey = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:model-prefix:authors:genealabslaravelmodelcachingtestsfixturesprefixedauthor-authors.deleted_at_null:http://localhost-first");
        $prefixTags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:model-prefix:genealabslaravelmodelcachingtestsfixturesprefixedauthor",
        ];
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null:http://localhost-first");
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
