<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures\Nova;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class BookResource extends Resource
{
    public static $model = Book::class;

    public static $search = ['id'];

    /**
     * {@inheritDoc}
     */
    public function fields(Request $request)
    {
        return [
            ID::make('ID', 'id'),
            Text::make('Title', 'title'),
            BelongsToMany::make('Stores', 'stores', StoreResource::class),
        ];
    }

    public static function uriKey()
    {
        return 'books';
    }
}
