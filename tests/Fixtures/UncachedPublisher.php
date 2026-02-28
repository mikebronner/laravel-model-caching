<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Tests\Database\Factories\UncachedPublisherFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UncachedPublisher extends Model
{
    use HasFactory;

    protected static function newFactory(): UncachedPublisherFactory
    {
        return UncachedPublisherFactory::new();
    }

    protected $fillable = [
        'name',
    ];

    protected $table = 'publishers';

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'publisher_id', 'id');
    }
}
