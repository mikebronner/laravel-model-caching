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
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];
        $cachedAuthors = $this
            ->cache()
            ->tags($tags)
            ->get($key)["value"]
            ?? null;

        $this->assertInstanceOf(Collection::class, $allAuthors);
        $this->assertNull($cachedAuthors);
    }

    public function testUsingTruncateInvalidatesCache()
    {
        (new Author)->get();
        Author::truncate();

        $this->assertTrue((new Author)->get()->isEmpty());
    }
}
