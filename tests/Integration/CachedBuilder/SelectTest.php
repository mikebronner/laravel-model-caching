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

class SelectTest extends IntegrationTestCase
{
    public function testSelectWithRawColumns()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::books:genealabslaravelmodelcachingtestsfixturesbook_orderBy_author_id_asc');
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesbook',
        ];
        $selectArray = [
            app("db")->raw("author_id"),
            app("db")->raw("AVG(id) AS averageIds"),
        ];

        $books = (new Book)
            ->select($selectArray)
            ->groupBy("author_id")
            ->orderBy("author_id")
            ->get()
            ->toArray();
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value']
            ->toArray();
        $liveResults = (new Book)
            ->select($selectArray)
            ->groupBy("author_id")
            ->orderBy("author_id")
            ->get()
            ->toArray();

        $this->assertEquals($liveResults, $books);
        $this->assertEquals($liveResults, $cachedResults);
    }
}
