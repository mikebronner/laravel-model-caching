<?php namespace GeneaLabs\LaravelModelCaching\Tests\Feature\Nova;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;

/** @group nova */
class CreateTest extends NovaTestCase
{
    public function testCreateFlushesCacheForModel()
    {
        $beforeAuthors = (new Author)->get();

        $this->postJson('nova-api/authors', [
            'name' => 'foo',
            'email' => 'test1@noemail.com',
        ]);

        $authors = (new Author)->get();

        $this->response->assertStatus(201);
        $this->assertCount(10, $beforeAuthors);
        $this->assertCount(11, $authors);
    }
}
