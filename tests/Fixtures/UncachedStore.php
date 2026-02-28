<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UncachedStore extends Model
{
    protected $fillable = [
        'address',
        'name',
    ];

    protected $table = 'stores';

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(UncachedBook::class);
    }
}
