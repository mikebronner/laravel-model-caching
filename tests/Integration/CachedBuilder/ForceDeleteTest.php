<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

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
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Http\Resources\Author as AuthorResource;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

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
                'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor',
            ])
            ->get(sha1(
                'genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor-id_=_1'
            ))["value"];

        (new Author)
            ->where("id", 1)
            ->forceDelete();
        $resultsAfter = $this
            ->cache()
            ->tags([
                'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor',
            ])
            ->get(sha1(
                'genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor-id_=_1'
            ))["value"];

        $this->assertEquals(get_class($resultsBefore), get_class($author));
        $this->assertNotNull($resultsBefore);
        $this->assertNull($resultsAfter);
    }
}
