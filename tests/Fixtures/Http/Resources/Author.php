<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Author extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'books' => $this->books,
        ];
    }
}
