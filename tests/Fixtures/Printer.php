<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Tests\Database\Factories\PrinterFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Printer extends Model
{
    use Cachable;
    use HasFactory;

    protected static function newFactory(): PrinterFactory
    {
        return PrinterFactory::new();
    }

    protected $fillable = [
        'book_id',
        'name',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
