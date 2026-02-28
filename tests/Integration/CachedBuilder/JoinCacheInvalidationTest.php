<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class JoinCacheInvalidationTest extends IntegrationTestCase
{
    public function testJoinQueryCacheTagsContainJoinedTableTag()
    {
        $author = (new Author)->first();
        $query = (new Author)
            ->select('authors.*', 'books.title')
            ->join('books', 'books.author_id', '=', 'authors.id');

        $tags = $query->makeCacheTags();

        // Should contain the primary model's class-based tag
        $this->assertContains(
            (new \Illuminate\Support\Str)->slug(Author::class),
            $tags,
            'Tags should include the primary model class tag'
        );

        // Should contain a table-based tag for the joined table
        $this->assertContains(
            (new \Illuminate\Support\Str)->slug('books'),
            $tags,
            'Tags should include the joined table tag'
        );
    }

    public function testJoinQueryCacheInvalidatesWhenJoinedModelUpdated()
    {
        // 1. Run a join query to warm the cache
        $results = (new Author)
            ->select('authors.*', 'books.title AS book_title')
            ->join('books', 'books.author_id', '=', 'authors.id')
            ->get();

        $this->assertGreaterThan(0, $results->count());

        // 2. Grab a book and update its title
        $book = (new Book)->first();
        $originalTitle = $book->title;
        $newTitle = 'Updated Title ' . uniqid();
        $book->title = $newTitle;
        $book->save();

        // 3. Re-run the same join query — should reflect the update, not stale cache
        $freshResults = (new Author)
            ->select('authors.*', 'books.title AS book_title')
            ->join('books', 'books.author_id', '=', 'authors.id')
            ->get();

        $matchingRow = $freshResults->firstWhere('id', $book->author_id);
        $this->assertNotNull($matchingRow);
        $this->assertEquals(
            $newTitle,
            $matchingRow->book_title,
            'Join query should return fresh data after the joined model is updated'
        );
    }

    public function testLeftJoinQueryCacheInvalidatesWhenJoinedModelUpdated()
    {
        // 1. Warm the cache with a left join query
        $results = (new Author)
            ->select('authors.*', 'books.title AS book_title')
            ->leftJoin('books', 'books.author_id', '=', 'authors.id')
            ->get();

        $this->assertGreaterThan(0, $results->count());

        // 2. Update a book
        $book = (new Book)->first();
        $newTitle = 'Left Join Updated ' . uniqid();
        $book->title = $newTitle;
        $book->save();

        // 3. Re-run — should be fresh
        $freshResults = (new Author)
            ->select('authors.*', 'books.title AS book_title')
            ->leftJoin('books', 'books.author_id', '=', 'authors.id')
            ->get();

        $matchingRow = $freshResults->firstWhere('id', $book->author_id);
        $this->assertNotNull($matchingRow);
        $this->assertEquals(
            $newTitle,
            $matchingRow->book_title,
            'Left join query should return fresh data after the joined model is updated'
        );
    }
}
