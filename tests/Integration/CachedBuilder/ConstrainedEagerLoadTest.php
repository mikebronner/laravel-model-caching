<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Publisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPublisher;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConstrainedEagerLoadTest extends IntegrationTestCase
{
    public function testConstrainedEagerLoadsProduceDifferentResults(): void
    {
        $publisher = Publisher::factory()->create();
        Book::factory()->create(['publisher_id' => $publisher->id, 'title' => 'Jason Bourne']);
        Book::factory()->create(['publisher_id' => $publisher->id, 'title' => 'Bason Journe']);

        $jasonBourneBooks = Publisher::with(['books' => function ($q) {
            $q->where('title', 'Jason Bourne');
        }])->get()->pluck('books')->flatten();

        $this->assertCount(1, $jasonBourneBooks);
        $this->assertEquals('Jason Bourne', $jasonBourneBooks->first()->title);

        $basonJournBooks = Publisher::with(['books' => function ($q) {
            $q->where('title', 'Bason Journe');
        }])->get()->pluck('books')->flatten();

        $this->assertCount(1, $basonJournBooks);
        $this->assertEquals(
                'Bason Journe',
                $basonJournBooks->first()->title,
                'Second constrained eager load should return different results than the first.',
            );
    }

    public function testConstrainedEagerLoadsProduceDistinctCacheKeys(): void
    {
        $publisher = Publisher::factory()->create(['name' => 'ZZZUNIQUE-Publisher']);
        sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:publishers:" .
            (new \GeneaLabs\LaravelModelCaching\CacheKey(
                ['books' => function ($q) { $q->where('title', 'Jason Bourne'); }],
                new Publisher,
                (new Publisher)->newQueryWithoutScopes()->getQuery(),
                '',
                [],
                false
            ))
            ->make(['*']));

        Book::factory()->create(['publisher_id' => $publisher->id, 'title' => 'Jason Bourne 2']);
        Book::factory()->create(['publisher_id' => $publisher->id, 'title' => 'Bason Journe 2']);

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

    public function testUnconstrainedEagerLoadStillWorks(): void
    {
        $publisher = Publisher::factory()->create();
        Book::factory()->count(3)->create(['publisher_id' => $publisher->id]);

        $publishers = Publisher::where('id', $publisher->id)->with('books')->get();
        $uncached   = (new UncachedPublisher)->where('id', $publisher->id)->with('books')->get();

        $this->assertEquals(
            $uncached->first()->books->pluck('id')->sort()->values()->toArray(),
            $publishers->first()->books->pluck('id')->sort()->values()->toArray()
        );
    }

    public function testThreeDistinctConstraintsReturnDistinctResults(): void
    {
        $publisher = Publisher::factory()->create();
        Book::factory()->create(['publisher_id' => $publisher->id, 'title' => 'Alpha Title']);
        Book::factory()->create(['publisher_id' => $publisher->id, 'title' => 'Beta Title']);
        Book::factory()->create(['publisher_id' => $publisher->id, 'title' => 'Gamma Title']);

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

    public function testConstrainedEagerLoadCacheIsInvalidatedOnRelationChange(): void
    {
        $publisher = Publisher::factory()->create();
        $book = Book::factory()->create([
            'publisher_id' => $publisher->id,
            'title' => 'Original Title',
        ]);

        $first = Publisher::where('id', $publisher->id)
            ->with(['books' => fn($q) => $q->where('title', 'Original Title')])
            ->get()
            ->pluck('books')
            ->flatten();

        $book->title = 'Updated Title';
        $book->save();

        $second = Publisher::where('id', $publisher->id)
            ->with(['books' => fn($q) => $q->where('title', 'Updated Title')])
            ->get()
            ->pluck('books')
            ->flatten();

        $this->assertCount(1, $first);
        $this->assertCount(1, $second);
        $this->assertEquals('Updated Title', $second->first()->title);
    }

    public function testDynamicLocalScopeInWithClosureProducesDistinctCacheKeys(): void
    {
        $authorA = Author::factory()->create(['name' => 'Author A']);
        $authorB = Author::factory()->create(['name' => 'Author B']);

        Book::factory()->create(['author_id' => $authorA->id, 'title' => 'Book for A']);
        Book::factory()->create(['author_id' => $authorB->id, 'title' => 'Book for B']);

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
        $this->assertEquals(
                'Book for B',
                $booksB->first()->title,
                'Changing the scope parameter should return different cached results.',
            );
    }
}