<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\Traits;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorWithDynamicFillable;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthorWithDynamicFillable;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class DynamicFillableTest extends IntegrationTestCase
{
    public function testDynamicFillableInConstructorWorksOnFirstSave()
    {
        // Create a model with dynamic fillable — the field added in constructor should be fillable
        $author = new AuthorWithDynamicFillable();
        $author->fill([
            'name' => 'Test Author',
            'email' => 'test@example.com',
            'is_famous' => true,
        ]);

        $this->assertTrue(
            $author->is_famous,
            'Dynamic fillable field should be set on first fill when using Cachable trait'
        );
    }

    public function testDynamicFillableMatchesUncachedBehavior()
    {
        // Uncached model with same dynamic fillable
        $uncached = new UncachedAuthorWithDynamicFillable();
        $uncached->fill([
            'name' => 'Uncached Author',
            'email' => 'uncached@example.com',
            'is_famous' => true,
        ]);

        // Cached model with same dynamic fillable
        $cached = new AuthorWithDynamicFillable();
        $cached->fill([
            'name' => 'Cached Author',
            'email' => 'cached@example.com',
            'is_famous' => true,
        ]);

        $this->assertEquals(
            $uncached->is_famous,
            $cached->is_famous,
            'Cachable and non-Cachable models should behave identically with dynamic fillable'
        );
    }

    public function testDynamicFillableFieldIsSavedOnFirstAttempt()
    {
        // Create and save a model with a dynamically-added fillable field
        $author = AuthorWithDynamicFillable::create([
            'name' => 'First Save Author',
            'email' => 'firstsave@example.com',
            'is_famous' => true,
        ]);

        // Reload from database (bypass cache) to confirm it was actually saved
        $fromDb = (new UncachedAuthorWithDynamicFillable())
            ->newQuery()
            ->where('email', 'firstsave@example.com')
            ->first();

        $this->assertNotNull($fromDb, 'Author should exist in database after first save');
        $this->assertTrue(
            (bool) $fromDb->is_famous,
            'Dynamic fillable field should be persisted on first save, not require a second save'
        );
    }

    public function testDynamicFillableFieldWorksWithUpdate()
    {
        // Create an author without is_famous
        $author = AuthorWithDynamicFillable::create([
            'name' => 'Update Author',
            'email' => 'update@example.com',
            'is_famous' => false,
        ]);

        // Now update the dynamically-filled field
        $author->update(['is_famous' => true]);

        // Reload from database to confirm
        $fromDb = (new UncachedAuthorWithDynamicFillable())
            ->newQuery()
            ->where('email', 'update@example.com')
            ->first();

        $this->assertTrue(
            (bool) $fromDb->is_famous,
            'Dynamic fillable field should be updated on first update attempt'
        );
    }

    public function testConstructorFillableChangesNotLostByTraitInitialization()
    {
        // Verify that the fillable array includes the dynamically-added field
        $author = new AuthorWithDynamicFillable();
        $fillable = $author->getFillable();

        $this->assertContains(
            'is_famous',
            $fillable,
            'Dynamic fillable field added in constructor should persist after trait initialization'
        );
    }

    public function testDynamicFillableOnCachedRetrievedModel()
    {
        // Create a model
        $author = AuthorWithDynamicFillable::create([
            'name' => 'Cache Test Author',
            'email' => 'cachetest@example.com',
            'is_famous' => false,
        ]);

        // Fetch via cached query (populates cache)
        $fetched = AuthorWithDynamicFillable::where('email', 'cachetest@example.com')->first();

        // Fetch again (should come from cache — deserialized, constructor doesn't run)
        $fromCache = AuthorWithDynamicFillable::where('email', 'cachetest@example.com')->first();

        // Try to update the dynamically-fillable field on the cached instance
        $fromCache->update(['is_famous' => true]);

        // Verify from DB
        $fromDb = (new UncachedAuthorWithDynamicFillable())
            ->newQuery()
            ->where('email', 'cachetest@example.com')
            ->first();

        $this->assertTrue(
            (bool) $fromDb->is_famous,
            'Dynamic fillable field should work on cached (deserialized) model instances'
        );
    }

    public function testFillableArraySurvivesSerialization()
    {
        $original = new AuthorWithDynamicFillable();
        $this->assertContains('is_famous', $original->getFillable());

        // Simulate what cache does
        $serialized = serialize($original);
        $deserialized = unserialize($serialized);

        $this->assertContains(
            'is_famous',
            $deserialized->getFillable(),
            'Dynamic fillable should survive serialization/deserialization'
        );
    }
}
