<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class SelectTest extends IntegrationTestCase
{
    public function testSelectWithRawColumns()
    {
        $key = sha1('genealabs:laravel-model-caching:testing::memory::books:genealabslaravelmodelcachingtestsfixturesbook_author_id_AVG(id) AS averageIds_orderBy_author_id_asc');
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

    // public function testSelectFieldsAreCached()
    // {
    //     $key = sha1('genealabs:laravel-model-caching:testing::memory::authors:genealabslaravelmodelcachingtestsfixturesauthor_id_name-first');
    //     $tags = [
    //         'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesauthor',
    //     ];

    //     $authorFields = (new Author)
    //         ->select("id", "name")
    //         ->first()
    //         ->getAttributes();
    //     $uncachedFields = (new UncachedAuthor)
    //         ->select("id", "name")
    //         ->first()
    //         ->getAttributes();
    //     $cachedFields = $this
    //         ->cache()
    //         ->tags($tags)
    //         ->get($key)['value']
    //         ->getAttributes();

    //     $this->assertEquals($cachedFields, $authorFields);
    //     $this->assertEquals($cachedFields, $uncachedFields);
    // }
}
