<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Feature\Nova;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\NovaTestCase;

class UpdateTest extends NovaTestCase
{
    public function test_update_flushes_cache_for_model()
    {
        $beforeAuthors = (new Author)->get();
        $author = $beforeAuthors->first();

        $response = $this->putJson('nova-api/authors/'.$author->id, [
            'name' => 'foo',
            'email' => 'test1@noemail.com',
        ]);

        $authors = (new Author)->get();

        $response->assertStatus(200);
        $this->assertCount(10, $beforeAuthors);
        $this->assertCount(10, $authors);

        $updatedAuthor = $authors->first();
        $this->assertTrue($updatedAuthor->is($author));
        $this->assertSame('foo', $updatedAuthor->name);
        $this->assertSame('test1@noemail.com', $updatedAuthor->email);

        $author->refresh();
        $this->assertSame('foo', $author->name);
        $this->assertSame('test1@noemail.com', $author->email);
    }
}
