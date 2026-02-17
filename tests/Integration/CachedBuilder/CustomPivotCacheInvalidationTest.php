<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\CachedBelongsToMany;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Role;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\User;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Support\Facades\DB;

/**
 * Regression tests for issue #481: Cache invalidation when using custom
 * intermediate table models (via ->using(CustomPivot::class)).
 *
 * Acceptance criteria:
 *   [AC1] Cache is invalidated when pivotAttached, pivotDetached, and pivotSynced
 *         events fire via a custom pivot model.
 *   [AC2] A regression test covering custom intermediate table models (->using()) is added.
 *   [AC3] Existing pivot invalidation behaviour is not regressed.
 */
class CustomPivotCacheInvalidationTest extends IntegrationTestCase
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function userIdWithRoles(): int
    {
        return (int) DB::table('role_user')->value('user_id');
    }

    private function getCacheTagsAndKey(CachedBelongsToMany $relation): array
    {
        $reflTags = new \ReflectionMethod($relation, 'makeCacheTags');
        $reflTags->setAccessible(true);
        $tags = $reflTags->invoke($relation);

        $reflKey = new \ReflectionMethod($relation, 'makeCacheKey');
        $reflKey->setAccessible(true);
        $rawKey = $reflKey->invoke($relation);

        return [$tags, sha1($rawKey)];
    }

    // -------------------------------------------------------------------------
    // Sanity check: relationship with custom pivot returns CachedBelongsToMany
    // -------------------------------------------------------------------------

    public function testCustomPivotRelationshipReturnsCachedBelongsToMany(): void
    {
        $userId = $this->userIdWithRoles();
        $relation = (new User)->find($userId)->rolesWithCustomPivot();

        $this->assertInstanceOf(
            CachedBelongsToMany::class,
            $relation,
            'rolesWithCustomPivot() should return CachedBelongsToMany even when using() is set.'
        );
    }

    // -------------------------------------------------------------------------
    // AC1 + AC2: Cache is invalidated on attach via custom pivot model
    // -------------------------------------------------------------------------

    public function testCacheIsInvalidatedWhenAttachingViaCustomPivot(): void
    {
        $userId = $this->userIdWithRoles();

        $relation = (new User)->find($userId)->rolesWithCustomPivot();
        [$tags, $hashedKey] = $this->getCacheTagsAndKey($relation);

        // Warm the cache.
        $result = (new User)->find($userId)->rolesWithCustomPivot;
        $this->assertNotEmpty($result);

        // Attach via the custom-pivot relationship — cache must be busted.
        $newRole = factory(Role::class)->create();
        (new User)->find($userId)->rolesWithCustomPivot()->attach($newRole->id);

        $cachedResult = $this->cache()->tags($tags)->get($hashedKey);

        $this->assertNull(
            $cachedResult,
            'Expected cache to be invalidated after attach via custom pivot, but a cached value was found.'
        );
    }

    // -------------------------------------------------------------------------
    // AC1 + AC2: Cache is invalidated on detach via custom pivot model
    // -------------------------------------------------------------------------

    public function testCacheIsInvalidatedWhenDetachingViaCustomPivot(): void
    {
        $userId = $this->userIdWithRoles();

        $relation = (new User)->find($userId)->rolesWithCustomPivot();
        [$tags, $hashedKey] = $this->getCacheTagsAndKey($relation);

        // Warm the cache.
        $result = (new User)->find($userId)->rolesWithCustomPivot;
        $this->assertNotEmpty($result);

        $firstRoleId = $result->first()->id;
        (new User)->find($userId)->rolesWithCustomPivot()->detach($firstRoleId);

        $cachedResult = $this->cache()->tags($tags)->get($hashedKey);

        $this->assertNull(
            $cachedResult,
            'Expected cache to be invalidated after detach via custom pivot, but a cached value was found.'
        );
    }

    // -------------------------------------------------------------------------
    // AC1 + AC2: Cache is invalidated on sync via custom pivot model
    // -------------------------------------------------------------------------

    public function testCacheIsInvalidatedWhenSyncingViaCustomPivot(): void
    {
        $userId = $this->userIdWithRoles();

        $relation = (new User)->find($userId)->rolesWithCustomPivot();
        [$tags, $hashedKey] = $this->getCacheTagsAndKey($relation);

        // Warm the cache.
        $result = (new User)->find($userId)->rolesWithCustomPivot;
        $this->assertNotEmpty($result);

        $newRoles = factory(Role::class, 2)->create();
        (new User)->find($userId)->rolesWithCustomPivot()->sync($newRoles->pluck('id'));

        $cachedResult = $this->cache()->tags($tags)->get($hashedKey);

        $this->assertNull(
            $cachedResult,
            'Expected cache to be invalidated after sync via custom pivot, but a cached value was found.'
        );

        // Verify roles match what was synced.
        $this->assertEmpty(array_diff(
            (new User)->find($userId)->rolesWithCustomPivot->pluck('id')->toArray(),
            $newRoles->pluck('id')->toArray()
        ));
    }

    // -------------------------------------------------------------------------
    // AC3: Existing (non-custom-pivot) invalidation still works
    // -------------------------------------------------------------------------

    public function testExistingPivotInvalidationNotRegressed(): void
    {
        $userId = $this->userIdWithRoles();

        $relation = (new User)->find($userId)->roles();
        [$tags, $hashedKey] = $this->getCacheTagsAndKey($relation);

        // Warm the cache.
        $result = (new User)->find($userId)->roles;
        $this->assertNotEmpty($result);

        $newRole = factory(Role::class)->create();
        (new User)->find($userId)->roles()->attach($newRole->id);

        $cachedResult = $this->cache()->tags($tags)->get($hashedKey);

        $this->assertNull(
            $cachedResult,
            'Existing (non-custom-pivot) cache invalidation on attach should still work.'
        );
    }
}
