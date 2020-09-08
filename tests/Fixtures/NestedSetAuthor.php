<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;

class NestedSetAuthor extends Model
{
    use Cachable;
    use NodeTrait;
    use SoftDeletes;

    protected $casts = [
        "finances" => "array",
    ];
    protected $fillable = [
        'name',
        'email',
        "finances",
    ];

    public function books() : HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function printers() : HasManyThrough
    {
        return $this->hasManyThrough(Printer::class, Book::class);
    }

    public function profile() : HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function getLatestBookAttribute()
    {
        return $this
            ->books()
            ->latest("id")
            ->first();
    }

    public function scopeStartsWithA(Builder $query) : Builder
    {
        return $query->where('name', 'LIKE', 'A%');
    }

    public function scopeNameStartsWith(Builder $query, string $startOfName) : Builder
    {
        return $query->where("name", "LIKE", "{$startOfName}%");
    }

    public function scopeBooksStartWith(Builder $query, string $startOfName) : Builder
    {
        return $query->where("name", "LIKE", "{$startOfName}%");
    }
}
