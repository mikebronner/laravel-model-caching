<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Observers\RoleUserObserver;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Role;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\RoleUser;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\User;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Support\Facades\DB;

/**
 * Regression tests for issue #548: Pivot model observers don't fire
 * when model caching is enabled.
 *
 * Acceptance criteria:
 *   [AC1] Observers registered on custom pivot models fire correctly when
 *         model caching is enabled.
 *   [AC2] sync(), attach(), and detach() operations on relationships with
 *         custom pivots trigger pivot model events.
 */
class PivotModelObserverTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        RoleUser::observe(RoleUserObserver::class);
        RoleUserObserver::reset();
    }

    protected function tearDown(): void
    {
        RoleUserObserver::reset();

        parent::tearDown();
    }

    private function userIdWithRoles(): int
    {
        return (int) DB::table('role_user')->value('user_id');
    }

    // -------------------------------------------------------------------------
    // AC1 + AC2: Observer fires on sync() with caching enabled
    // -------------------------------------------------------------------------

    public function test_pivot_observer_fires_on_sync_with_caching_enabled(): void
    {
        $userId = $this->userIdWithRoles();
        $user = (new User)->find($userId);

        // Warm the cache by accessing the relationship.
        $user->rolesWithCustomPivot;

        // Sync to new roles — should fire creating/created on the pivot observer.
        $newRoles = Role::factory()->count(2)->create();
        RoleUserObserver::reset();
        $user->rolesWithCustomPivot()->sync($newRoles->pluck('id')->toArray());

        $this->assertGreaterThan(
            0,
            RoleUserObserver::$events['creating'] ?? 0,
            'Pivot observer "creating" event should fire during sync() with caching enabled.'
        );
        $this->assertGreaterThan(
            0,
            RoleUserObserver::$events['created'] ?? 0,
            'Pivot observer "created" event should fire during sync() with caching enabled.'
        );
    }

    // -------------------------------------------------------------------------
    // AC2: Observer fires on attach() with caching enabled
    // -------------------------------------------------------------------------

    public function test_pivot_observer_fires_on_attach_with_caching_enabled(): void
    {
        $userId = $this->userIdWithRoles();
        $user = (new User)->find($userId);

        $newRole = Role::factory()->create();
        RoleUserObserver::reset();
        $user->rolesWithCustomPivot()->attach($newRole->id);

        $this->assertGreaterThan(
            0,
            RoleUserObserver::$events['creating'] ?? 0,
            'Pivot observer "creating" event should fire during attach() with caching enabled.'
        );
        $this->assertGreaterThan(
            0,
            RoleUserObserver::$events['created'] ?? 0,
            'Pivot observer "created" event should fire during attach() with caching enabled.'
        );
    }

    // -------------------------------------------------------------------------
    // AC2: Observer fires on detach() with caching enabled
    // -------------------------------------------------------------------------

    public function test_pivot_observer_fires_on_detach_with_caching_enabled(): void
    {
        $userId = $this->userIdWithRoles();
        $user = (new User)->find($userId);
        $roles = $user->rolesWithCustomPivot;
        $this->assertNotEmpty($roles);

        $firstRoleId = $roles->first()->id;
        RoleUserObserver::reset();
        $user->rolesWithCustomPivot()->detach($firstRoleId);

        $this->assertGreaterThan(
            0,
            RoleUserObserver::$events['deleting'] ?? 0,
            'Pivot observer "deleting" event should fire during detach() with caching enabled.'
        );
        $this->assertGreaterThan(
            0,
            RoleUserObserver::$events['deleted'] ?? 0,
            'Pivot observer "deleted" event should fire during detach() with caching enabled.'
        );
    }
}
