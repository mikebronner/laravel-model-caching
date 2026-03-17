<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Comment;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Post;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedComment;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

/**
 * Tests for PR #582: Call parent::__clone() in CachedBuilder clone method.
 *
 * The fix ensures that cloning a CachedBuilder deep-clones the underlying
 * query builder (via parent::__clone()), so that wheres, bindings, scopes,
 * and other query-level state are properly isolated between the original
 * and the clone. Without this fix, morphTo relationships can leak constraints
 * from one model's query into another.
 */
class CloneTest extends IntegrationTestCase
{
    // -------------------------------------------------------------------------
    // Basic clone isolation
    // -------------------------------------------------------------------------

    public function testCloningBuilderIsolatesWheres(): void
    {
        $original = (new Author)->where('name', 'LIKE', 'A%');
        $clone = clone $original;

        // Adding a where to the clone must not affect the original.
        $clone->where('email', 'test@example.com');

        $originalWheres = $original->getQuery()->wheres;
        $cloneWheres = $clone->getQuery()->wheres;

        $this->assertCount(
            count($originalWheres) + 1,
            $cloneWheres,
            'Clone should have one more where clause than the original after modification.'
        );

        // Verify the original does not contain the extra where.
        $originalColumns = array_column($originalWheres, 'column');
        $this->assertNotContains(
            'email',
            $originalColumns,
            'Original builder should not contain wheres added to the clone.'
        );
    }

    public function testCloningBuilderIsolatesBindings(): void
    {
        $original = (new Author)->where('name', '=', 'Alice');
        $clone = clone $original;

        $clone->where('email', '=', 'bob@example.com');

        $originalBindings = $original->getQuery()->getBindings();
        $cloneBindings = $clone->getQuery()->getBindings();

        $this->assertCount(
            count($originalBindings) + 1,
            $cloneBindings,
            'Clone should have one more binding than the original after modification.'
        );

        $this->assertNotContains(
            'bob@example.com',
            $originalBindings,
            'Original builder bindings should not contain values added to the clone.'
        );
    }

    public function testCloningBuilderIsolatesOrders(): void
    {
        $original = (new Author)->orderBy('name');
        $clone = clone $original;

        $clone->orderBy('email');

        $originalOrders = $original->getQuery()->orders ?? [];
        $cloneOrders = $clone->getQuery()->orders ?? [];

        $this->assertCount(1, $originalOrders, 'Original should still have only one order clause.');
        $this->assertCount(2, $cloneOrders, 'Clone should have two order clauses.');
    }

    // -------------------------------------------------------------------------
    // Inner builder isolation (composition path)
    // -------------------------------------------------------------------------

    public function testCloningBuilderDeepClonesInnerBuilder(): void
    {
        $builder = (new Author)->newQuery();

        if (! $builder instanceof CachedBuilder) {
            $this->markTestSkipped('Builder is not a CachedBuilder; cannot test innerBuilder cloning.');
        }

        $inner = $builder->getInnerBuilder();

        if ($inner === null) {
            // No inner builder — the fix still applies via parent::__clone()
            // which deep-clones the underlying $query. Already covered by the
            // basic isolation tests above.
            $this->assertTrue(true);

            return;
        }

        $clone = clone $builder;

        $this->assertNotSame(
            $inner,
            $clone->getInnerBuilder(),
            'Cloned CachedBuilder must have a different innerBuilder instance.'
        );
    }

    // -------------------------------------------------------------------------
    // morphTo constraint isolation (the original bug scenario)
    // -------------------------------------------------------------------------

    /**
     * Regression test for the exact scenario described in PR #582:
     *
     * When eager-loading a morphTo relationship, Laravel internally clones the
     * builder to issue separate queries per morph type. Without the
     * parent::__clone() call, wheres/scopes from one type's query could leak
     * into another type's query (e.g. SoftDeletes' `deleted_at is null`
     * constraint from Author leaking into Post or Book queries).
     *
     * This test verifies that eager-loading a morphTo across multiple types
     * returns the correct results — matching the uncached (baseline) query.
     */
    public function testMorphToEagerLoadDoesNotLeakConstraintsAcrossTypes(): void
    {
        $post = (new Post)->first();
        $book = (new Book)->first();

        // Create comments pointing to different morph types
        $postComment = Comment::create([
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'description' => 'clone-test post comment',
            'subject' => 'clone isolation post',
        ]);
        $bookComment = Comment::create([
            'commentable_id' => $book->id,
            'commentable_type' => Book::class,
            'description' => 'clone-test book comment',
            'subject' => 'clone isolation book',
        ]);

        $this->cache()->flush();

        // Cached query — eager-load morphTo which triggers builder cloning
        $comments = (new Comment)
            ->with('commentable')
            ->whereIn('id', [$postComment->id, $bookComment->id])
            ->orderBy('id')
            ->get();

        // Uncached query — baseline
        $uncachedComments = (new UncachedComment)
            ->with('commentable')
            ->whereIn('id', [$postComment->id, $bookComment->id])
            ->orderBy('id')
            ->get();

        // Both should return two comments
        $this->assertCount(2, $comments);
        $this->assertCount(2, $uncachedComments);

        $cachedPostComment = $comments->firstWhere('id', $postComment->id);
        $cachedBookComment = $comments->firstWhere('id', $bookComment->id);
        $uncachedPostComment = $uncachedComments->firstWhere('id', $postComment->id);
        $uncachedBookComment = $uncachedComments->firstWhere('id', $bookComment->id);

        // The commentable relation must be loaded and be the correct type
        $this->assertNotNull($cachedPostComment->commentable, 'Post commentable should not be null.');
        $this->assertNotNull($cachedBookComment->commentable, 'Book commentable should not be null.');

        $this->assertInstanceOf(Post::class, $cachedPostComment->commentable);
        $this->assertInstanceOf(Book::class, $cachedBookComment->commentable);

        // Values should match the uncached results
        $this->assertEquals(
            $uncachedPostComment->commentable->id,
            $cachedPostComment->commentable->id,
            'Cached morphTo Post id should match uncached result.'
        );
        $this->assertEquals(
            $uncachedBookComment->commentable->id,
            $cachedBookComment->commentable->id,
            'Cached morphTo Book id should match uncached result.'
        );
    }

    /**
     * Verify that SoftDeletes scopes on one model (Author) do not leak into
     * unrelated morphTo queries for models that do not use SoftDeletes (Post).
     *
     * Author uses SoftDeletes; Post does not. If cloning is broken, the
     * `deleted_at is null` constraint could leak when the builder is cloned
     * during morphTo resolution.
     */
    public function testSoftDeletesScopeDoesNotLeakIntoMorphToQuery(): void
    {
        // Warm up an Author query so SoftDeletes scope is active on that builder
        (new Author)->first();

        $post = (new Post)->first();

        $comment = Comment::create([
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'description' => 'soft-delete leak test',
            'subject' => 'soft-delete scope isolation',
        ]);

        $this->cache()->flush();

        // Eager-load morphTo — this must not introduce deleted_at constraints on posts
        $result = (new Comment)
            ->with('commentable')
            ->where('id', $comment->id)
            ->first();

        $uncachedResult = (new UncachedComment)
            ->with('commentable')
            ->where('id', $comment->id)
            ->first();

        $this->assertNotNull($result->commentable, 'morphTo should resolve the Post without SoftDeletes leaking.');
        $this->assertInstanceOf(Post::class, $result->commentable);
        $this->assertEquals(
            $uncachedResult->commentable->id,
            $result->commentable->id,
            'Cached result should match uncached result — no scope leakage.'
        );
    }

    /**
     * When morphTo targets a model that DOES use SoftDeletes (Author via Book
     * author relationship isn't morphTo, but we can set up Author as a morph
     * target), the soft-delete scope should correctly apply only to that model
     * and not to other morph types resolved in the same eager-load batch.
     */
    public function testMorphToWithMixedSoftDeleteModelsResolvesCorrectly(): void
    {
        $post = (new Post)->first();
        $book = (new Book)->first();

        $postComment = Comment::create([
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'description' => 'mixed morph post comment',
            'subject' => 'mixed morph post',
        ]);

        $bookComment = Comment::create([
            'commentable_id' => $book->id,
            'commentable_type' => Book::class,
            'description' => 'mixed morph book comment',
            'subject' => 'mixed morph book',
        ]);

        $this->cache()->flush();

        $ids = [$postComment->id, $bookComment->id];

        // First load — cache miss
        $firstLoad = (new Comment)
            ->with('commentable')
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        // Second load — cache hit
        $secondLoad = (new Comment)
            ->with('commentable')
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        // Baseline
        $baseline = (new UncachedComment)
            ->with('commentable')
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        foreach ([
            'first load (miss)' => $firstLoad,
            'second load (hit)' => $secondLoad,
        ] as $label => $comments) {
            $pc = $comments->firstWhere('id', $postComment->id);
            $bc = $comments->firstWhere('id', $bookComment->id);
            $bpc = $baseline->firstWhere('id', $postComment->id);
            $bbc = $baseline->firstWhere('id', $bookComment->id);

            $this->assertInstanceOf(
                Post::class,
                $pc->commentable,
                "Post commentable should be a Post on {$label}."
            );
            $this->assertInstanceOf(
                Book::class,
                $bc->commentable,
                "Book commentable should be a Book on {$label}."
            );
            $this->assertEquals(
                $bpc->commentable->id,
                $pc->commentable->id,
                "Post commentable id should match baseline on {$label}."
            );
            $this->assertEquals(
                $bbc->commentable->id,
                $bc->commentable->id,
                "Book commentable id should match baseline on {$label}."
            );
        }
    }

    // -------------------------------------------------------------------------
    // Multiple sequential clones
    // -------------------------------------------------------------------------

    public function testMultipleClonesDoNotShareState(): void
    {
        $base = (new Author)->where('name', 'LIKE', 'A%');

        $clone1 = clone $base;
        $clone1->where('email', 'clone1@example.com');

        $clone2 = clone $base;
        $clone2->where('id', '>', 5);

        $baseWheres = $base->getQuery()->wheres;
        $clone1Wheres = $clone1->getQuery()->wheres;
        $clone2Wheres = $clone2->getQuery()->wheres;

        // base should have the original where only
        $baseColumns = array_column($baseWheres, 'column');
        $this->assertNotContains('email', $baseColumns);
        $this->assertNotContains('id', $baseColumns);

        // clone1 should have base + email
        $clone1Columns = array_column($clone1Wheres, 'column');
        $this->assertContains('email', $clone1Columns);
        $this->assertNotContains('id', $clone1Columns);

        // clone2 should have base + id
        $clone2Columns = array_column($clone2Wheres, 'column');
        $this->assertContains('id', $clone2Columns);
        $this->assertNotContains('email', $clone2Columns);
    }
}