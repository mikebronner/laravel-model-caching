<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class UncachedAuthor extends Model
{
    use SoftDeletes;
    
    protected $casts = [
        "finances" => "array",
    ];
    protected $fillable = [
        'name',
        'email',
        "finances",
    ];
    protected $table = 'authors';

    public function books() : HasMany
    {
        return $this->hasMany(UncachedBook::class, 'author_id', 'id');
    }

    public function getLatestBookAttribute()
    {
        return $this
            ->books()
            ->latest("id")
            ->first();
    }

    public function printer() : HasOneThrough
    {
        return $this->hasOneThrough(
            Printer::class,
            Book::class,
            "author_id"
        );
    }

    public function profile() : HasOne
    {
        return $this->hasOne(UncachedProfile::class, 'author_id', 'id');
    }

    public function scopeStartsWithA(Builder $query) : Builder
    {
        return $query->where('name', 'LIKE', 'A%');
    }

    public function scopeNameStartsWith(Builder $query, string $startOfName) : Builder
    {
        return $query->where("name", "LIKE", "{$startOfName}%");
    }
}
