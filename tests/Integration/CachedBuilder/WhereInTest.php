<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class WhereInTest extends IntegrationTestCase
{
    public function test_where_in_using_collection_query()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:books:genealabslaravelmodelcachingtestsfixturesbook-author_id_in_1_2_3_4");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesbook",
        ];
        $authors = (new UncachedAuthor)
            ->where('id', '<', 5)
            ->get(['id']);

        $books = (new Book)
            ->whereIn('author_id', $authors)
            ->get();
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedBook)
            ->whereIn('author_id', $authors)
            ->get();

        $this->assertEquals($liveResults->pluck('id'), $books->pluck('id'));
        $this->assertEquals($liveResults->pluck('id'), $cachedResults->pluck('id'));
    }

    public function test_where_in_when_set_is_empty()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-id_in_-authors.deleted_at_null");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];
        $authors = (new Author)
            ->whereIn('id', [])
            ->get();
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->whereIn('id', [])
            ->get();

        $this->assertEquals($liveResults->pluck('id'), $authors->pluck('id'));
        $this->assertEquals($liveResults->pluck('id'), $cachedResults->pluck('id'));
    }

    public function test_bindings_are_correct_with_multiple_where_in_clauses()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-name_in_John-id_in_-name_in_Mike-authors.deleted_at_null");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];
        $authors = (new Author)
            ->whereIn('name', ['John'])
            ->whereIn('id', [])
            ->whereIn('name', ['Mike'])
            ->get();
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->whereIn('name', ['Mike'])
            ->whereIn('id', [])
            ->whereIn('name', ['John'])
            ->get();

        $this->assertEquals($liveResults->pluck('id'), $authors->pluck('id'));
        $this->assertEquals($liveResults->pluck('id'), $cachedResults->pluck('id'));
    }

    public function test_where_in_uses_correct_bindings()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-id_in_1_2_3_4_5-id_between_1_99999-authors.deleted_at_null");
        $tags = ["genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor"];

        $authors = (new Author)
            ->whereIn('id', [1, 2, 3, 4, 5])
            ->whereBetween('id', [1, 99999])
            ->get();
        $cachedResults = $this->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->whereIn('id', [1, 2, 3, 4, 5])
            ->whereBetween('id', [1, 99999])
            ->get();

        $this->assertEmpty($authors->diffKeys($cachedResults));
        $this->assertEmpty($liveResults->diffKeys($cachedResults));
    }

    public function test_where_in_with_percent_character_in_value_does_not_throw()
    {
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:authors:genealabslaravelmodelcachingtestsfixturesauthor-name_in_10%_20%-authors.deleted_at_null");
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturesauthor",
        ];

        $authors = (new Author)
            ->whereIn('name', ['10%', '20%'])
            ->get();
        $cachedResults = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $liveResults = (new UncachedAuthor)
            ->whereIn('name', ['10%', '20%'])
            ->get();

        $this->assertEquals($liveResults->pluck('id'), $authors->pluck('id'));
        $this->assertEquals($liveResults->pluck('id'), $cachedResults->pluck('id'));
    }

    public function test_where_in_with_subquery_containing_single_where_clause()
    {
        $books = (new Book)
            ->whereIn('author_id', function ($query) {
                $query->select('id')
                    ->from('authors')
                    ->where('name', '=', 'John');
            })
            ->get();

        $liveResults = (new UncachedBook)
            ->whereIn('author_id', function ($query) {
                $query->select('id')
                    ->from('authors')
                    ->where('name', '=', 'John');
            })
            ->get();

        $this->assertEquals($liveResults->pluck('id'), $books->pluck('id'));
    }

    public function test_where_in_with_subquery_containing_multiple_where_clauses()
    {
        $books = (new Book)
            ->whereIn('author_id', function ($query) {
                $query->select('id')
                    ->from('authors')
                    ->where('name', '=', 'John')
                    ->where('id', '>', 0);
            })
            ->get();

        $liveResults = (new UncachedBook)
            ->whereIn('author_id', function ($query) {
                $query->select('id')
                    ->from('authors')
                    ->where('name', '=', 'John')
                    ->where('id', '>', 0);
            })
            ->get();

        $this->assertEquals($liveResults->pluck('id'), $books->pluck('id'));
    }

    public function test_nested_where_not_in_with_subquery_does_not_crash_with_uuid_exception()
    {
        $books = (new Book)
            ->whereNotIn('author_id', function ($query) {
                $query->select('id')
                    ->from('authors')
                    ->where('name', '=', 'John');
            })
            ->whereNotIn('author_id', function ($query) {
                $query->select('id')
                    ->from('authors')
                    ->where('name', '=', 'Mike');
            })
            ->get();

        $liveResults = (new UncachedBook)
            ->whereNotIn('author_id', function ($query) {
                $query->select('id')
                    ->from('authors')
                    ->where('name', '=', 'John');
            })
            ->whereNotIn('author_id', function ($query) {
                $query->select('id')
                    ->from('authors')
                    ->where('name', '=', 'Mike');
            })
            ->get();

        $this->assertEquals($liveResults->pluck('id'), $books->pluck('id'));
    }

    public function test_where_in_with_non_uuid_string_values_skips_from_bytes()
    {
        $books = (new Book)
            ->whereIn('author_id', function ($query) {
                $query->selectRaw('distinct id')
                    ->from('authors');
            })
            ->get();

        $liveResults = (new UncachedBook)
            ->whereIn('author_id', function ($query) {
                $query->selectRaw('distinct id')
                    ->from('authors');
            })
            ->get();

        $this->assertEquals($liveResults->pluck('id'), $books->pluck('id'));
    }
}
