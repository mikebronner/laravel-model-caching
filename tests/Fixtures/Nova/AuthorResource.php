<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures\Nova;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class AuthorResource extends Resource
{
    public static $model = Author::class;

    public static $search = ['id'];

    /**
     * @inheritDoc
     */
    public function fields(Request $request)
    {
        return [
            ID::make('ID', 'id'),
            Text::make('Name', 'name'),
            Text::make('E-Mail', 'email'),
        ];
    }

    public static function uriKey()
    {
        return 'authors';
    }
}
