<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\Traits;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorWithConditionalFillable;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\AuthorWithDynamicFillable;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthorWithDynamicFillable;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class DynamicFillableTest extends IntegrationTestCase
{
    public function tearDown(): void
    {
        AuthorWithConditionalFillable::$adminMode = false;

        parent::tearDown();
    }

    public function test_dynamic_fillable_in_constructor_works_on_first_save()
    {
        $author = new AuthorWithDynamicFillable;
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

    public function test_dynamic_fillable_matches_uncached_behavior()
    {
        $uncached = new UncachedAuthorWithDynamicFillable;
        $uncached->fill([
            'name' => 'Uncached Author',
            'email' => 'uncached@example.com',
            'is_famous' => true,
        ]);

        $cached = new AuthorWithDynamicFillable;
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

    public function test_dynamic_fillable_field_is_saved_on_first_attempt()
    {
        $author = AuthorWithDynamicFillable::create([
            'name' => 'First Save Author',
            'email' => 'firstsave@example.com',
            'is_famous' => true,
        ]);

        $fromDb = (new UncachedAuthorWithDynamicFillable)
            ->newQuery()
            ->where('email', 'firstsave@example.com')
            ->first();

        $this->assertNotNull($fromDb, 'Author should exist in database after first save');
        $this->assertTrue(
            (bool) $fromDb->is_famous,
            'Dynamic fillable field should be persisted on first save, not require a second save'
        );
    }

    public function test_dynamic_fillable_field_works_with_update()
    {
        $author = AuthorWithDynamicFillable::create([
            'name' => 'Update Author',
            'email' => 'update@example.com',
            'is_famous' => false,
        ]);

        $author->update(['is_famous' => true]);

        $fromDb = (new UncachedAuthorWithDynamicFillable)
            ->newQuery()
            ->where('email', 'update@example.com')
            ->first();

        $this->assertTrue(
            (bool) $fromDb->is_famous,
            'Dynamic fillable field should be updated on first update attempt'
        );
    }

    public function test_constructor_fillable_changes_not_lost_by_trait_initialization()
    {
        $author = new AuthorWithDynamicFillable;
        $fillable = $author->getFillable();

        $this->assertContains(
            'is_famous',
            $fillable,
            'Dynamic fillable field added in constructor should persist after trait initialization'
        );
    }

    /**
     * Regression test for #534: model cached in non-admin context must
     * reflect admin-context fillable when deserialized by an admin.
     *
     * Without the __wakeup fix, the deserialized model retains the stale
     * $fillable from the original (non-admin) context, silently dropping
     * admin-only fields on mass assignment.
     */
    public function test_deserialized_model_reruns_constructor_for_dynamic_fillable()
    {
        // Cache the model in non-admin context — is_famous NOT in $fillable
        AuthorWithConditionalFillable::$adminMode = false;
        $original = new AuthorWithConditionalFillable;
        $this->assertNotContains('is_famous', $original->getFillable());

        $serialized = serialize($original);

        // Switch to admin context
        AuthorWithConditionalFillable::$adminMode = true;

        // Deserialize — simulates what cache retrieval does
        $deserialized = unserialize($serialized);

        // With __wakeup, the constructor re-runs in admin context
        $this->assertContains(
            'is_famous',
            $deserialized->getFillable(),
            'Deserialized model must re-run constructor so dynamic $fillable reflects current context'
        );
    }

    /**
     * Regression test for #534: admin-only fillable field must be mass-
     * assignable on a model retrieved from cache that was originally
     * cached in a non-admin context.
     */
    public function test_cached_model_update_works_after_context_change()
    {
        // Create author in admin mode
        AuthorWithConditionalFillable::$adminMode = true;
        $author = AuthorWithConditionalFillable::create([
            'name' => 'Context Test Author',
            'email' => 'context@example.com',
            'is_famous' => false,
        ]);

        // Flush cache, switch to non-admin, populate cache
        $author->flushCache();
        AuthorWithConditionalFillable::$adminMode = false;
        AuthorWithConditionalFillable::where('email', 'context@example.com')->first();

        // Switch back to admin, fetch from cache, update admin-only field
        AuthorWithConditionalFillable::$adminMode = true;
        $fromCache = AuthorWithConditionalFillable::where('email', 'context@example.com')->first();
        $fromCache->update(['is_famous' => true]);

        // Verify in DB
        $fromDb = (new UncachedAuthorWithDynamicFillable)
            ->newQuery()
            ->where('email', 'context@example.com')
            ->first();

        $this->assertTrue(
            (bool) $fromDb->is_famous,
            'Admin-only fillable field must work on model cached in non-admin context'
        );
    }
}
