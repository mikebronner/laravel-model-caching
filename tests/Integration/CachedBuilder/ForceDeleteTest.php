<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class ForceDeleteTest extends IntegrationTestCase
{
    public function testForceDeleteClearsCache()
    {
        $author = (new Author)
            ->where("id", 1)
            ->get();

        $resultsBefore = $this
            ->cache()
            ->tags([
                "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            ])
            ->get(sha1(
                "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-id_=_1-authors.deleted_at_null"
            ))["value"];

        (new Author)
            ->where("id", 1)
            ->forceDelete();
        $resultsAfter = $this
            ->cache()
            ->tags([
                "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            ])
            ->get(sha1(
                "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-id_=_1"
            ))["value"]
            ?? null;

        $this->assertEquals(get_class($resultsBefore), get_class($author));
        $this->assertNotNull($resultsBefore);
        $this->assertNull($resultsAfter);
    }
}
