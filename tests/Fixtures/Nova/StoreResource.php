<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures\Nova;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class StoreResource extends Resource
{
    public static $model = Store::class;

    public static $search = ['id'];

    /**
     * {@inheritDoc}
     */
    public function fields(Request $request)
    {
        return [
            ID::make('ID', 'id'),
            Text::make('Name', 'name'),
            Text::make('Address', 'address'),
        ];
    }

    public static function uriKey()
    {
        return 'stores';
    }
}
