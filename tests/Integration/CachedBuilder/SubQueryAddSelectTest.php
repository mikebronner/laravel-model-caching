<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPublisher;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Database\Eloquent\Collection;

class SubQueryAddSelectTest extends IntegrationTestCase
{
    public function testAddSelect()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:books:genealabslaravelmodelcachingtestsfixturesbook_books.*_(select \"name\" from \"publishers\" where \"id\" = \"books\".\"publisher_id\" order by \"published_at\" desc limit 1) as \"publisher_name\"-publisher_id_in_11_12_13_14_15:http://localhost");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook",
        ];

        /** @var Collection $publishers */
        $publishers = factory(UncachedPublisher::class, 5)->create();

        $publishers->each(function (UncachedPublisher $publisher) {
            factory(UncachedBook::class, 2)->create(['publisher_id' => $publisher->id]);
        });

        $publisherIds = $publishers->pluck('id')->toArray();

        $books = Book::whereIn('publisher_id', $publisherIds)
            ->addSelect(['publisher_name' =>
                Publisher::select('name')
                ->whereColumn('id', 'books.publisher_id')
                ->orderBy('published_at', 'desc')
                ->limit(1)
            ])->get()->pluck('publisher_name')->filter()->toArray();

        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];

        $liveResults = UncachedBook::whereIn('publisher_id', $publisherIds)
            ->addSelect(['publisher_name' =>
                UncachedPublisher::select('name')
                ->whereColumn('id', 'books.publisher_id')
                ->orderBy('published_at', 'desc')
                ->limit(1)
            ])->get()->pluck('publisher_name')->filter()->toArray();

        $this->assertCount(10, $books);
        $this->assertSame($liveResults, $books);
        $this->assertSame($liveResults, $cachedResults->pluck('publisher_name')->filter()->toArray());
    }
}
