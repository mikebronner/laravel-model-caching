<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\Traits;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\PrefixedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Profile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedProfile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPublisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedStore;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;

class BuilderCachingTest extends IntegrationTestCase
{
    public function testDisablingAllQuery()
    {
        $allAuthors = (new Author)
            ->disableCache()
            ->all();
        $key = sha1("genealabs:laravel-model-caching:testing::memory::test-prefix:authors:genealabslaravelmodelcachingtestsfixturesauthor");
        $tags = [
            "genealabs:laravel-model-caching:testing::memory::test-prefix:genealabslaravelmodelcachingtestsfixturesauthor",
        ];
        $cachedAuthors = $this
            ->cache()
            ->tags($tags)
            ->get($key)["value"];

        $this->assertInstanceOf(Collection::class, $allAuthors);
        $this->assertNull($cachedAuthors);
    }
}
