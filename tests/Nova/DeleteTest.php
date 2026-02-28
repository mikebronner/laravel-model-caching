<?php namespace GeneaLabs\LaravelModelCaching\Tests\Feature\Nova;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\NovaTestCase;

class DeleteTest extends NovaTestCase
{
    public function testDeleteFlushesCacheForModel()
    {
        $beforeAuthors = (new Author)->get();
        $deleteAuthor = $beforeAuthors->first();

        $response = $this->deleteJson('nova-api/authors', ['resources' => [$deleteAuthor->id]]);

        $authors = (new Author)->get();

        $response->assertStatus(200);
        $this->assertCount(10, $beforeAuthors);
        $this->assertCount(9, $authors);
    }
}
