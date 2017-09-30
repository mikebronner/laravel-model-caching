<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Author extends Resource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'books' => $this->books,
        ];
    }
}
