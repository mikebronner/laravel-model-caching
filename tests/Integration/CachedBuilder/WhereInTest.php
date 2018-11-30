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

class WhereInTest extends IntegrationTestCase
{
    public function testWithInUsingCollectionQuery()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::books:genealabslaravelmodelcachingtestsfixturesbook-author_id_in_1_2_3_4');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook',
        ];
        $authors = (new UncachedAuthor)
            ->where("id", "<", 5)
            ->get(["id"]);

        $books = (new Book)
            ->whereIn("author_id", $authors)
            ->get();
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedBook)
            ->whereIn("author_id", $authors)
            ->get();

        $this->assertEquals($liveResults->pluck("id"), $books->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
    }
}
