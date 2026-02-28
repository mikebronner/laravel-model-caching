<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class UncachedSupplier extends Model
{
    protected $fillable = [
        'name',
    ];

    protected $table = 'suppliers';

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function history(): HasOneThrough
    {
        return $this->hasOneThrough(
            History::class,
            User::class,
            'supplier_id',
            'user_id'
        );
    }
}
