<?php namespace GeneaLabs\LaravelModelCaching\Tests\Unit;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Profile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedProfile;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPublisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedStore;
use GeneaLabs\LaravelModelCaching\Tests\UnitTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DisabledCachedModelTest extends UnitTestCase
{
    use RefreshDatabase;

    public function testAllModelResultsIsNotCached()
    {
        $key = sha1('genealabslaravelmodelcachingtestsfixturesauthor');
        $tags = ['genealabslaravelmodelcachingtestsfixturesauthor'];
        $authors = (new Author)
            ->disableCache()
            ->all();

        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key);
        $liveResults = (new UncachedAuthor)
            ->all();

        $this->assertEmpty($liveResults->diffAssoc($authors));
        $this->assertNull($cachedResults);
    }
}
