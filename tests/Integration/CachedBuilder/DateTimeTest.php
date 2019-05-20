<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class DateTimeTest extends IntegrationTestCase
{
    public function testDateWhereCreatesCorrectCacheKey()
    {
        $dateTime = now()->subYears(10)->toDateString();
        $key = sha1("genealabs:laravel-model-caching:testing:/Users/mike/Developer/Sites/laravel-model-caching/tests/database/testing.sqlite:books:genealabslaravelmodelcachingtestsfixturesbook-publish_at_>_{$dateTime}");
        $tags = [
            'genealabs:laravel-model-caching:testing:/Users/mike/Developer/Sites/laravel-model-caching/tests/database/testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $results = (new Book)
            ->where("publish_at", ">", $dateTime)
            ->get();
        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedBook)
            ->where("publish_at", ">", $dateTime)
            ->get();

        $this->assertEquals($liveResults->pluck("id"), $results->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
        $this->assertNotEmpty($results);
        $this->assertNotEmpty($cachedResults);
        $this->assertNotEmpty($liveResults);
    }
}
