<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Custom pivot model for the role_user table.
 * Used to test cache invalidation via custom intermediate table models (issue #481).
 */
class RoleUser extends Pivot
{
    protected $table = 'role_user';

    protected $fillable = [
        'role_id',
        'user_id',
    ];
}
