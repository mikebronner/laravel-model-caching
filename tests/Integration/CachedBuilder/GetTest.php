<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class GetTest extends IntegrationTestCase
{
    public function test_get_model_results_creates_cache()
    {
        $authors = (new Author)
            ->with('books', 'profile')
            ->get();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-authors.deleted_at_null-testing:{$this->testingSqlitePath}testing.sqlite:books-testing:{$this->testingSqlitePath}testing.sqlite:profile");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesprofile",
        ];

        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->with('books', 'profile')
            ->get();

        $this->assertEquals($authors, $cachedResults);
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function test_accessing_get_results_via_array_index_does_not_error()
    {
        $author = (new Author)
            ->where('id', 1)
            ->get()[0];
        $cachedAuthor = (new Author)
            ->where('id', 1)
            ->get()[0];
        $uncachedAuthor = (new UncachedAuthor)
            ->where('id', 1)
            ->get()[0];

        $this->assertEquals(1, $author->id);
        $this->assertEquals($author, $cachedAuthor);
        $this->assertEquals($author->toArray(), $uncachedAuthor->toArray());
    }

    public function test_get_with_field_array_caches_results()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor_id_name-authors.deleted_at_null");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $authors = (new Author)
            ->get(['id', 'name']);
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->get(['id', 'name']);

        $this->assertEquals($liveResults->pluck('id'), $authors->pluck('id'));
        $this->assertEquals($liveResults->pluck('id'), $cachedResults->pluck('id'));
    }

    public function test_get_with_field_string_caches_results()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor_id-authors.deleted_at_null");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $authors = (new Author)
            ->get('id');
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->get('id');

        $this->assertEquals($liveResults->pluck('id'), $authors->pluck('id'));
        $this->assertEquals($liveResults->pluck('id'), $cachedResults->pluck('id'));
    }
}
