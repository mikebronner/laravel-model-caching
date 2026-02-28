<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Post;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Tag;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Support\Facades\DB;

/**
 * Regression tests for issue #538: Cache not invalidated on morphToMany
 * detach / HasManyThrough intermediate model changes.
 */
class MorphToManyCacheInvalidationTest extends IntegrationTestCase
{
    public function testMorphToManyAttachInvalidatesCache(): void
    {
        $post = (new Post)->first();
        $newTag = Tag::factory()->create(['name' => 'attach-tag']);

        $initialCount = $post->tags()->count();
        $post->tags()->attach($newTag->id);

        $cachedCount = $post->tags()->count();
        $dbCount = DB::table('taggables')
            ->where('taggable_id', $post->id)
            ->where('taggable_type', Post::class)
            ->count();

        $this->assertEquals($dbCount, $cachedCount, 'Cache should be invalidated after morphToMany attach.');
        $this->assertEquals($initialCount + 1, $cachedCount);
    }

    public function testMorphToManyDetachInvalidatesCache(): void
    {
        $post = (new Post)->first();
        $this->assertGreaterThan(0, $post->tags()->count(), 'Post should have at least one tag.');

        $post->tags()->count();
        $post->tags()->detach();

        $cachedCount = $post->tags()->count();
        $dbCount = DB::table('taggables')
            ->where('taggable_id', $post->id)
            ->where('taggable_type', Post::class)
            ->count();

        $this->assertEquals(0, $dbCount);
        $this->assertEquals($dbCount, $cachedCount, 'Cache should be invalidated after morphToMany detach.');
    }

    public function testMorphToManySyncInvalidatesCache(): void
    {
        $post = (new Post)->first();
        $newTags = Tag::factory()->count(3)->create();

        $post->tags()->count();
        $post->tags()->sync($newTags->pluck('id'));

        $cachedCount = $post->tags()->count();
        $dbCount = DB::table('taggables')
            ->where('taggable_id', $post->id)
            ->where('taggable_type', Post::class)
            ->count();

        $this->assertEquals(3, $dbCount);
        $this->assertEquals($dbCount, $cachedCount, 'Cache should be invalidated after morphToMany sync.');
    }
}
