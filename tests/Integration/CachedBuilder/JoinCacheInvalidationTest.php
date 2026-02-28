<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\CacheTags;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Support\Str;

class JoinCacheInvalidationTest extends IntegrationTestCase
{
    public function testJoinQueryCacheTagsContainJoinedTableTag()
    {
        $query = (new Author)
            ->select('authors.*', 'books.title')
            ->join('books', 'books.author_id', '=', 'authors.id');

        // CacheTags expects the Eloquent builder (CachedBuilder), which has getQuery()
        $tags = (new CacheTags(
            $query->getEagerLoads(),
            $query->getModel(),
            $query
        ))->make();

        $bookTableTag = (new Str)->slug('books');

        $this->assertTrue(
            collect($tags)->contains(function ($tag) {
                return str_contains($tag, (new Str)->slug(Author::class));
            }),
            'Tags should include the primary model class tag'
        );

        $this->assertTrue(
            collect($tags)->contains(function ($tag) use ($bookTableTag) {
                return str_contains($tag, $bookTableTag);
            }),
            'Tags should include the joined table tag'
        );
    }

    public function testJoinQueryCacheInvalidatesWhenJoinedModelUpdated()
    {
        $results = (new Author)
            ->select('authors.*', 'books.title AS book_title')
            ->join('books', 'books.author_id', '=', 'authors.id')
            ->get();

        $this->assertGreaterThan(0, $results->count());

        $book = (new Book)->first();
        $newTitle = 'Updated Title ' . uniqid();
        $book->title = $newTitle;
        $book->save();

        $freshResults = (new Author)
            ->select('authors.*', 'books.title AS book_title')
            ->join('books', 'books.author_id', '=', 'authors.id')
            ->get();

        $matchingRows = $freshResults->where('book_title', $newTitle);
        $this->assertGreaterThan(
            0,
            $matchingRows->count(),
            'Join query should return fresh data after the joined model is updated'
        );
    }

    public function testLeftJoinQueryCacheInvalidatesWhenJoinedModelUpdated()
    {
        $results = (new Author)
            ->select('authors.*', 'books.title AS book_title')
            ->leftJoin('books', 'books.author_id', '=', 'authors.id')
            ->get();

        $this->assertGreaterThan(0, $results->count());

        $book = (new Book)->first();
        $newTitle = 'Left Join Updated ' . uniqid();
        $book->title = $newTitle;
        $book->save();

        $freshResults = (new Author)
            ->select('authors.*', 'books.title AS book_title')
            ->leftJoin('books', 'books.author_id', '=', 'authors.id')
            ->get();

        $matchingRows = $freshResults->where('book_title', $newTitle);
        $this->assertGreaterThan(
            0,
            $matchingRows->count(),
            'Left join query should return fresh data after the joined model is updated'
        );
    }
}
