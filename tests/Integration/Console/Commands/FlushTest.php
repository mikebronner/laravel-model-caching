<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\Console\Commands;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\PrefixedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Support\Str;

class FlushTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (Str::startsWith($this->app->version(), '5.7')) {
            $this->withoutMockingConsoleOutput();
        }
    }

    public function test_given_model_is_flushed()
    {
        $authors = (new Author)->all();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor"];

        $cachedResults = $this
            ->cache
            ->tags($tags)
            ->get($key)['value'];
        $result = $this
            ->artisan('modelCache:clear', ['--model' => Author::class])
            ->execute();
        $flushedResults = $this
            ->cache
            ->tags($tags)
            ->get($key)['value']
            ?? null;

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($flushedResults);
        $this->assertEquals($result, 0);
    }

    public function test_extended_model_is_flushed()
    {
        $authors = (new PrefixedAuthor)
            ->get();

        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:model-prefix:authors:genealabslaravelmodelcachingtestsfixturesprefixedauthor-authors.deleted_at_null");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:model-prefix:genealabslaravelmodelcachingtestsfixturesprefixedauthor"];

        $cachedResults = $this
            ->cache
            ->tags($tags)
            ->get($key)['value'];
        $result = $this
            ->artisan('modelCache:clear', ['--model' => PrefixedAuthor::class])
            ->execute();
        $flushedResults = $this
            ->cache
            ->tags($tags)
            ->get($key)['value']
            ?? null;

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($flushedResults);
        $this->assertEquals($result, 0);
    }

    public function test_given_model_with_relationship_is_flushed()
    {
        $authors = (new Author)->with('books')->get();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null-testing:{$this->testingSqlitePath}testing.sqlite:books");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook",
        ];

        $cachedResults = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $result = $this
            ->artisan(
                'modelCache:clear',
                ['--model' => Author::class]
            )
            ->execute();
        $flushedResults = $this->cache
            ->tags($tags)
            ->get($key)['value']
            ?? null;

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($flushedResults);
        $this->assertEquals($result, 0);
    }

    public function test_non_cached_models_cannot_be_flushed()
    {
        $result = $this->artisan(
            'modelCache:clear',
            ['--model' => UncachedAuthor::class]
        )
            ->execute();

        $this->assertEquals($result, 1);
    }

    public function test_all_models_are_flushed()
    {
        (new Author)->all();
        (new Book)->all();
        (new Store)->all();

        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor"];
        $cachedAuthors = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:books:genealabslaravelmodelcachingtestsfixturesbook");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook"];
        $cachedBooks = $this->cache
            ->tags($tags)
            ->get($key)['value'];
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:stores:genealabslaravelmodelcachingtestsfixturesstore");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesstore"];
        $cachedStores = $this->cache
            ->tags($tags)
            ->get($key)['value'];

        $this->assertNotEmpty($cachedAuthors);
        $this->assertNotEmpty($cachedBooks);
        $this->assertNotEmpty($cachedStores);

        $this->artisan('modelCache:clear')
            ->execute();

        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor"];
        $cachedAuthors = $this->cache
            ->tags($tags)
            ->get($key)['value']
            ?? null;
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:books:genealabslaravelmodelcachingtestsfixturesbook");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook"];
        $cachedBooks = $this->cache
            ->tags($tags)
            ->get($key)['value']
            ?? null;
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:stores:genealabslaravelmodelcachingtestsfixturesstore");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesstore"];
        $cachedStores = $this->cache
            ->tags($tags)
            ->get($key)['value']
            ?? null;

        $this->assertEmpty($cachedAuthors);
        $this->assertEmpty($cachedBooks);
        $this->assertEmpty($cachedStores);
    }
}
