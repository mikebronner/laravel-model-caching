<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPublisher;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Database\Eloquent\Collection;

class SubQueryOrderByTest extends IntegrationTestCase
{
    public function testOrderByDesc()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:books:genealabslaravelmodelcachingtestsfixturesbook-publisher_id_in_11_12_13_14_15_orderBy_(select \"name\" from \"publishers\" where \"id\" = \"books\".\"publisher_id\" limit 1)_desc:http://localhost");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook",
        ];

        /** @var Collection $publishers */
        $publishers = factory(UncachedPublisher::class, 5)->create();

        $publishers->each(function (UncachedPublisher $publisher) {
            factory(UncachedBook::class, 2)->create(['publisher_id' => $publisher->id]);
        });

        $publisherIds = $publishers->pluck('id')->toArray();

        $books = Book::whereIn('publisher_id', $publisherIds)->orderByDesc(
            Publisher::select('name')
            ->whereColumn('id', 'books.publisher_id')
            ->limit(1)
        ) ->get()->pluck('id')->filter()->toArray();

        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];

        $liveResults = UncachedBook::whereIn('publisher_id', $publisherIds)->orderByDesc(
            UncachedPublisher::select('name')
            ->whereColumn('id', 'books.publisher_id')
            ->limit(1)
        )->get()->pluck('id')->filter()->toArray();

        $this->assertCount(10, $books);
        $this->assertSame($liveResults, $books);
        $this->assertSame($liveResults, $cachedResults->pluck('id')->filter()->toArray());
    }
}
