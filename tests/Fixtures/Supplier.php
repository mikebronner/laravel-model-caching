<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Tests\Database\Factories\SupplierFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Supplier extends Model
{
    use Cachable;
    use HasFactory;

    protected static function newFactory(): SupplierFactory
    {
        return SupplierFactory::new();
    }

    protected $fillable = [
        'name',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function history(): HasOneThrough
    {
        return $this->hasOneThrough(History::class, User::class);
    }
}
