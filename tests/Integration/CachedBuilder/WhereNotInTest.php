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

class WhereNotInTest extends IntegrationTestCase
{
    public function testWhereNotInQuery()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook-author_id_not_in_1_2_3_4');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook',
        ];
        $authors = (new UncachedAuthor)
            ->where("id", "<", 5)
            ->get(["id"]);

        $books = (new Book)
            ->whereNotIn("author_id", $authors)
            ->get();
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedBook)
            ->whereNotIn("author_id", $authors)
            ->get();

        $this->assertEquals($liveResults->pluck("id"), $books->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
    }

    public function testWhereNotInResults()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook-id_not_in_1_2');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $results = (new Book)
            ->whereNotIn('id', [1, 2])
            ->get();
        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedBook)
            ->whereNotIn('id', [1, 2])
            ->get();

        $this->assertEquals($liveResults->pluck("id"), $results->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
    }

    public function testWhereNotInSubquery()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook-id_notinsub_book-id_<_10');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook',
        ];

        $results = (new Book)
            ->whereNotIn("id", function ($query) {
                $query->select("id")->from("authors")->where("id", "<", 10);
            })
            ->get();
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedBook)
            ->whereNotIn("id", function ($query) {
                $query->select("id")->from("authors")->where("id", "<", 10);
            })
            ->get();

        $this->assertEquals($liveResults->pluck("id"), $results->pluck("id"));
        $this->assertEquals($liveResults->pluck("id"), $cachedResults->pluck("id"));
    }
}
