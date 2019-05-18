<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\Traits;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Database\Eloquent\Collection;

class BuilderCachingTest extends IntegrationTestCase
{
    public function testDisablingAllQuery()
    {
        $allAuthors = (new Author)
            ->disableCache()
            ->all();
        $key = sha1("genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor");
        $tags = [
            "genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor",
        ];
        $cachedAuthors = $this
            ->cache()
            ->tags($tags)
            ->get($key)["value"];

        $this->assertInstanceOf(Collection::class, $allAuthors);
        $this->assertNull($cachedAuthors);
    }
}
