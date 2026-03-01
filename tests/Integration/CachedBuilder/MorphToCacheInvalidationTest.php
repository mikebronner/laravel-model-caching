<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Comment;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Post;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Tag;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

/**
 * Tests for issue #544: Cache not invalidated on morphTo / morphedByMany delete.
 */
class MorphToCacheInvalidationTest extends IntegrationTestCase
{
    // -------------------------------------------------------------------------
    // AC1: Deleting a morphTo child invalidates the parent model's cache
    // -------------------------------------------------------------------------

    public function testDeletingMorphToChildInvalidatesParentCache(): void
    {
        $post = (new Post)->with('comments')->first();
        $this->assertNotEmpty($post->comments);

        // Warm the cache by querying comments through the parent
        $cachedComments = (new Post)->with('comments')->first()->comments;
        $this->assertNotEmpty($cachedComments);

        // Build the cache tag for Post
        $tags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturespost",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:posts",
        ];

        // Verify cache is populated (any key with this tag)
        $cachedPost = (new Post)->first();
        $key = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:posts:genealabslaravelmodelcachingtestsfixturespost-first");
        $cachedResult = $this->cache()->tags($tags)->get($key);
        $this->assertNotNull($cachedResult);

        // Delete a comment (morphTo child)
        $comment = $post->comments->first();
        $comment->delete();

        // Post cache should be invalidated
        $afterDeleteResult = $this->cache()->tags($tags)->get($key);
        $this->assertNull(
            $afterDeleteResult,
            'Post cache should be invalidated after deleting a morphTo child Comment.'
        );
    }

    // -------------------------------------------------------------------------
    // AC2: Attach via morphToMany / morphedByMany invalidates caches
    // -------------------------------------------------------------------------

    public function testAttachViaMorphToManyInvalidatesCache(): void
    {
        $post = (new Post)->first();

        // Warm the tag cache
        $tags = (new Tag)->all();
        $this->assertNotEmpty($tags);

        $tagCacheTags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturestag",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:tags",
        ];

        $tagKey = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:tags:genealabslaravelmodelcachingtestsfixturestag");
        $cachedTags = $this->cache()->tags($tagCacheTags)->get($tagKey);
        $this->assertNotNull($cachedTags);

        // Attach a new tag via morphToMany
        $newTag = Tag::factory()->create();
        $post->tags()->attach($newTag->id);

        // Both Post and Tag caches should be flushed
        $postCacheTags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturespost",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:posts",
        ];
        $postKey = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:posts:genealabslaravelmodelcachingtestsfixturespost-first");
        $afterAttachPost = $this->cache()->tags($postCacheTags)->get($postKey);
        $afterAttachTags = $this->cache()->tags($tagCacheTags)->get($tagKey);

        $this->assertNull(
            $afterAttachPost,
            'Post cache should be invalidated after attaching via morphToMany.'
        );
        $this->assertNull(
            $afterAttachTags,
            'Tag cache should be invalidated after attaching via morphToMany.'
        );
    }

    // -------------------------------------------------------------------------
    // AC2: Detach via morphToMany / morphedByMany invalidates caches
    // -------------------------------------------------------------------------

    public function testDetachViaMorphToManyInvalidatesCache(): void
    {
        $post = (new Post)->first();

        // Ensure post has tags
        $postTags = $post->tags;
        $this->assertNotEmpty($postTags);

        // Warm caches
        (new Post)->first();
        (new Tag)->all();

        $postCacheTags = [
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturespost",
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:posts",
        ];
        $postKey = sha1("genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:posts:genealabslaravelmodelcachingtestsfixturespost-first");
        $this->assertNotNull($this->cache()->tags($postCacheTags)->get($postKey));

        // Detach a tag
        $post->tags()->detach($postTags->first()->id);

        $afterDetachPost = $this->cache()->tags($postCacheTags)->get($postKey);
        $this->assertNull(
            $afterDetachPost,
            'Post cache should be invalidated after detaching via morphToMany.'
        );
    }
}
