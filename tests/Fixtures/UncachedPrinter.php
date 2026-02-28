<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UncachedPrinter extends Model
{
    protected $fillable = [
        'book_id',
        'name',
    ];

    protected $table = 'printer';

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }
}
