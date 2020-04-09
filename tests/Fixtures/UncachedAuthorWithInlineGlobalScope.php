<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class UncachedAuthorWithInlineGlobalScope extends Model
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
    protected $table = "authors";

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('inlineScope', function (Builder $builder) {
            return $builder->where('name', 'LIKE', "A%");
        });
    }

    public function books() : HasMany
    {
        return $this->hasMany(UncachedBook::class);
    }

    public function printers() : HasManyThrough
    {
        return $this->hasManyThrough(Printer::class, Book::class, "author_id");
    }

    public function profile() : HasOne
    {
        return $this->hasOne(UncachedProfile::class);
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
}
