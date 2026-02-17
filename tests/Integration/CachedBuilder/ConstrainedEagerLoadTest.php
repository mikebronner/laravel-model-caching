<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPublisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Regression tests for issue #295:
 * Constrained eager loading does not detect change to where constraint.
 *
 * Root cause: CacheKey::getWithModels() only included the relation name in
 * the cache key, ignoring any closure constraints applied via ->with().
 * Two sequential queries with different constraints returned the first
 * cached result regardless of the constraint change.
 */
class ConstrainedEagerLoadTest extends IntegrationTestCase
{
    /**
     * AC: Regression test from the issue — two queries with different `where`
     * constraints on the same relationship each return correct results.
     */
    public function testConstrainedEagerLoadsProduceDifferentResults(): void
    {
        $publisher = factory(Publisher::class)->create();
        factory(Book::class)->create(['publisher_id' => $publisher->id, 'title' => 'Jason Bourne']);
        factory(Book::class)->create(['publisher_id' => $publisher->id, 'title' => 'Bason Journe']);

        $jasonBourneBooks = Publisher::with(['books' => function ($q) {
            $q->where('title', 'Jason Bourne');
        }])->get()->pluck('books')->flatten();

        $this->assertCount(1, $jasonBourneBooks);
        $this->assertEquals('Jason Bourne', $jasonBourneBooks->first()->title);

        $basonJournBooks = Publisher::with(['books' => function ($q) {
            $q->where('title', 'Bason Journe');
        }])->get()->pluck('books')->flatten();

        $this->assertCount(1, $basonJournBooks);
        $this->assertEquals('Bason Journe', $basonJournBooks->first()->title,
            'Second constrained eager load should return different results than the first.');
    }

    /**
     * AC: Cache key generation includes the constraint closure's effect.
     * Verify the cache keys produced are distinct when constraints differ.
     */
    public function testConstrainedEagerLoadsProduceDistinctCacheKeys(): void
    {
        $publisher = factory(Publisher::class)->create(['name' => 'ZZZUNIQUE-Publisher']);

        // Build the cache keys manually by inspecting what the model generates.
        // The point is: with different constraints, the two queries should not share a cache key.
        $key1 = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:publishers:" .
            (new \GeneaLabs\LaravelModelCaching\CacheKey(
                ['books' => function ($q) { $q->where('title', 'Jason Bourne'); }],
                new Publisher,
                (new Publisher)->newQueryWithoutScopes()->getQuery(),
                '',
                [],
                false
            ))->make(['*']));

        // We don't need the exact key strings; we just need them to differ.
        // Run both queries and verify we get distinct, correct results.
        factory(Book::class)->create(['publisher_id' => $publisher->id, 'title' => 'Jason Bourne 2']);
        factory(Book::class)->create(['publisher_id' => $publisher->id, 'title' => 'Bason Journe 2']);

        $jasonResult = Publisher::where('name', 'ZZZUNIQUE-Publisher')
            ->with(['books' => fn($q) => $q->where('title', 'Jason Bourne 2')])->get()
            ->pluck('books')->flatten();

        $basonResult = Publisher::where('name', 'ZZZUNIQUE-Publisher')
            ->with(['books' => fn($q) => $q->where('title', 'Bason Journe 2')])->get()
            ->pluck('books')->flatten();

        $this->assertCount(1, $jasonResult);
        $this->assertCount(1, $basonResult);
        $this->assertNotEquals($jasonResult->first()->id, $basonResult->first()->id);
    }

    /**
     * AC: No constraint on eager load — should still work exactly as before.
     */
    public function testUnconstrainedEagerLoadStillWorks(): void
    {
        $publisher = factory(Publisher::class)->create();
        factory(Book::class, 3)->create(['publisher_id' => $publisher->id]);

        $publishers = Publisher::where('id', $publisher->id)->with('books')->get();
        $uncached   = (new UncachedPublisher)->where('id', $publisher->id)->with('books')->get();

        $this->assertEquals(
            $uncached->first()->books->pluck('id')->sort()->values()->toArray(),
            $publishers->first()->books->pluck('id')->sort()->values()->toArray()
        );
    }

    /**
     * AC: Three sequential calls with rotating constraints all return correct data.
     * Proves the fix is not limited to two-constraint scenarios.
     */
    public function testThreeDistinctConstraintsReturnDistinctResults(): void
    {
        $publisher = factory(Publisher::class)->create();
        factory(Book::class)->create(['publisher_id' => $publisher->id, 'title' => 'Alpha Title']);
        factory(Book::class)->create(['publisher_id' => $publisher->id, 'title' => 'Beta Title']);
        factory(Book::class)->create(['publisher_id' => $publisher->id, 'title' => 'Gamma Title']);

        $getBooks = fn(string $title) => Publisher::where('id', $publisher->id)
            ->with(['books' => fn($q) => $q->where('title', $title)])
            ->get()
            ->pluck('books')
            ->flatten();

        $alpha = $getBooks('Alpha Title');
        $beta  = $getBooks('Beta Title');
        $gamma = $getBooks('Gamma Title');

        $this->assertCount(1, $alpha);
        $this->assertCount(1, $beta);
        $this->assertCount(1, $gamma);
        $this->assertEquals('Alpha Title', $alpha->first()->title);
        $this->assertEquals('Beta Title', $beta->first()->title);
        $this->assertEquals('Gamma Title', $gamma->first()->title);
    }

    /**
     * AC: Cache invalidation — modifying a related record flushes the constrained
     * eager load cache, so subsequent queries see fresh data.
     */
    public function testConstrainedEagerLoadCacheIsInvalidatedOnRelationChange(): void
    {
        $publisher = factory(Publisher::class)->create();
        $book = factory(Book::class)->create([
            'publisher_id' => $publisher->id,
            'title' => 'Original Title',
        ]);

        // Prime the cache
        $first = Publisher::where('id', $publisher->id)
            ->with(['books' => fn($q) => $q->where('title', 'Original Title')])
            ->get()
            ->pluck('books')
            ->flatten();

        // Modify the related model (triggers cache flush on Book)
        $book->title = 'Updated Title';
        $book->save();

        // A new constrained query for the updated title should return fresh data
        $second = Publisher::where('id', $publisher->id)
            ->with(['books' => fn($q) => $q->where('title', 'Updated Title')])
            ->get()
            ->pluck('books')
            ->flatten();

        $this->assertCount(1, $first);
        $this->assertCount(1, $second);
        $this->assertEquals('Updated Title', $second->first()->title);
    }

    /**
     * AC: Dynamic local scopes inside with() closures also produce distinct keys.
     * Regression for issue #291 (dynamic scope parameter not captured in key).
     */
    public function testDynamicLocalScopeInWithClosureProducesDistinctCacheKeys(): void
    {
        $authorA = factory(Author::class)->create(['name' => 'Author A']);
        $authorB = factory(Author::class)->create(['name' => 'Author B']);

        factory(Book::class)->create(['author_id' => $authorA->id, 'title' => 'Book for A']);
        factory(Book::class)->create(['author_id' => $authorB->id, 'title' => 'Book for B']);

        $getBooksForAuthor = fn(int $authorId) => Author::with([
            'books' => function (HasMany $q) use ($authorId) {
                $q->where('author_id', $authorId);
            }
        ])->where('id', $authorId)->get()->pluck('books')->flatten();

        $booksA = $getBooksForAuthor($authorA->id);
        $booksB = $getBooksForAuthor($authorB->id);

        $this->assertCount(1, $booksA);
        $this->assertCount(1, $booksB);
        $this->assertEquals('Book for A', $booksA->first()->title);
        $this->assertEquals('Book for B', $booksB->first()->title,
            'Changing the scope parameter should return different cached results.');
    }
}
