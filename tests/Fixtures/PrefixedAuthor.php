<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrefixedAuthor extends Model
{
    use Cachable;
    use SoftDeletes;

    protected $cachePrefix = 'model-prefix';

    protected $casts = [
        'finances' => 'array',
    ];

    protected $fillable = [
        'name',
        'email',
        'finances',
    ];

    protected $table = 'authors';

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function getLatestBookAttribute()
    {
        return $this
            ->books()
            ->latest('id')
            ->first();
    }

    public function scopeStartsWithA(Builder $query): Builder
    {
        return $query->where('name', 'LIKE', 'A%');
    }

    public function scopeNameStartsWith(Builder $query, string $startOfName): Builder
    {
        return $query->where('name', 'LIKE', "{$startOfName}%");
    }
}
