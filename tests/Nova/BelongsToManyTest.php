<?php namespace GeneaLabs\LaravelModelCaching\Tests\Feature\Nova;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\NovaTestCase;

class BelongsToManyTest extends NovaTestCase
{
    public function testAttachRelationFlushesCache()
    {
        $beforeStore = Store::with(['books'])->get()->first();
        $beforeBooks = $beforeStore->books;

        $beforeBook = $beforeStore->books->first()->replicate();
        $beforeBook->title = 'new foo';
        $beforeBook->save();

        $this->postJson('nova-api/books/' . $beforeBook->id . '/attach/stores' , [
            'stores' => $beforeStore->id,
            'viaRelationship' => 'stores'
        ]);

        $this->response->assertStatus(200);

        $store = Store::with(['books'])->all()->first();
        $books = $store->books;
        $book = $store->books->sortByDesc('id')->first();

        $this->assertTrue($beforeStore->is($store));
        $this->assertTrue($beforeBook->is($book));
        $this->assertCount(1, $beforeBooks);
        $this->assertCount(2, $books);
        $this->assertCount(0, $beforeBook->stores);
        $this->assertSame('new foo', $book->title);
    }

    public function testDetachRelationFlushesCache()
    {
        $store = Store::with(['books'])->get()->first();
        $newBook = $store->books->first()->replicate();
        $newBook->title = 'new foo';
        $newBook->save();

        $store->books()->attach($newBook);

        $beforeStore = Store::with(['books'])->get()->first();
        $beforeBooks = $beforeStore->books;
        $beforeBook = $beforeBooks->first();

        $this->deleteJson('/nova-api/stores/detach?viaResource=books&viaResourceId=' . $beforeBook->id  . '&viaRelationship=stores' , [
            'resources' => [$beforeStore->id],
        ]);

        $this->response->assertStatus(200);

        $store = Store::with(['books'])->all()->first();
        $books = $store->books;

        $this->assertTrue($beforeStore->is($store));
        $this->assertCount(2, $beforeBooks);
        $this->assertCount(1, $books);
    }

    public function testUpdateRelationFlushesCache()
    {
        $beforeStore = Store::with(['books'])->get()->first();
        $beforeBook = $beforeStore->books->first();

        $this->putJson('nova-api/books/' . $beforeBook->id, [
            'title' => 'foo',
        ]);

        $store = Store::with(['books'])->all()->first();
        $book = $store->books->first();

        $this->response->assertStatus(200);

        $this->assertTrue($beforeStore->is($store));
        $this->assertTrue($beforeBook->is($book));
        $this->assertSame('foo', $book->title);
    }

    public function testDeleteRelationFlushesCache()
    {
        $beforeStore = Store::with(['books'])->get()->first();
        $beforeBooks = $beforeStore->books;
        $beforeBook = $beforeBooks->first();

        $this->deleteJson('nova-api/books', ['resources' => [$beforeBook->id]]);

        $store = Store::with(['books'])->all()->first();
        $books = $store->books;

        $this->response->assertStatus(200);

        $this->assertTrue($beforeStore->is($store));
        $this->assertCount(1, $beforeBooks);
        $this->assertCount(0, $books);
        $this->assertNull(Book::find($beforeBook->id));
    }
}
