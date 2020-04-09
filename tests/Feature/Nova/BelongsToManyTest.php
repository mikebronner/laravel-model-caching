<?php namespace GeneaLabs\LaravelModelCaching\Tests\Feature\Nova;

class BelongsToManyTest extends NovaTestCase
{
    /** @group test */
    public function testBasicNovaIndexRequest()
    {
        $this->getJson('nova-api/authors');
        // $response->dump();

        $this->response->assertStatus(200)
            ->assertJsonCount(10, 'resources');
    }
}
