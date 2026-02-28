<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UncachedHistory extends Model
{
    protected $fillable = [
        'name',
        'user_id',
    ];

    protected $table = 'histories';

    public function user(): BelongsTo
    {
        return $this->belongsTo(UncachedUser::class, 'user_id');
    }
}
