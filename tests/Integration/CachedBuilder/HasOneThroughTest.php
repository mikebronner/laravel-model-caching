<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class HasOneThroughTest extends IntegrationTestCase
{
    public function testEagerloadedHasOneThrough()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null-authors.id_=_1-testing:{$this->testingSqlitePath}testing.sqlite:printer-first");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesprinter",
        ];

        $printer = (new Author)
            ->with("printer")
            ->find(1)
            ->printer;
        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value']
            ->printer;
        $liveResults = (new UncachedAuthor)
            ->with("printer")
            ->find(1)
            ->printer;

        $this->assertEquals($liveResults->id, $printer->id);
        $this->assertEquals($liveResults->id, $cachedResults->id);
        $this->assertNotEmpty($printer);
        $this->assertNotEmpty($cachedResults);
        $this->assertNotEmpty($liveResults);
    }

    public function testLazyloadedHasMany()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null-authors.id_=_1-limit_1");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesprinter",
        ];

        // $printer = (new Author)
        //     ->find(1)
        //     ->printer;
        // $cachedResults = $this->cache()
        //     ->tags($tags)
        //     ->get($key)['value']
        //     ->printer;
        // $liveResults = (new UncachedAuthor)
        //     ->find(1)
        //     ->printer;

        // $this->assertEquals($liveResults->id, $printer->id);
        // $this->assertEquals($liveResults->id, $cachedResults->id);
        // $this->assertNotEmpty($printer);
        // $this->assertNotEmpty($cachedResults);
        // $this->assertNotEmpty($liveResults);
        $this->markTestSkipped();
    }
}
