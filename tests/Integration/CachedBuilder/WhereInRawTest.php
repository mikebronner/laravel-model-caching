<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class WhereInRawTest extends IntegrationTestCase
{
    public function testWhereInRawUsingRelationship()
    {
        $key = sha1('genealabs:laravel-model-caching:testing:/Users/mike/Developer/Sites/laravel-model-caching/tests/database/testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-testing:/Users/mike/Developer/Sites/laravel-model-caching/tests/database/testing.sqlite:books');
        $tags = [
            'genealabs:laravel-model-caching:testing:/Users/mike/Developer/Sites/laravel-model-caching/tests/database/testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor',
            'genealabs:laravel-model-caching:testing:/Users/mike/Developer/Sites/laravel-model-caching/tests/database/testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $authors = (new Author)
            ->with("books")
            ->get();
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->with("books")
            ->get();

        $this->assertEquals($liveResults->pluck("id"), $authors->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
    }
}
