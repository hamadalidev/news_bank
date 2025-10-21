<?php

namespace Tests\Feature\Api;

use App\Models\Author;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_authors_list(): void
    {
        Author::factory(12)->create();

        $response = $this->getJson(route('api.v1.authors.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'list' => [
                        '*' => [
                            'id',
                            'name',
                        ],
                    ],
                    'pagination' => [
                        'total',
                        'count',
                        'per_page',
                        'current_page',
                        'total_pages',
                    ],
                ],
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals(10, $response->json('data.pagination.count')); // Default page size
        $this->assertEquals(12, $response->json('data.pagination.total'));
    }

    public function test_can_search_authors(): void
    {
        Author::factory()->create(['name' => 'John Doe']);
        Author::factory()->create(['name' => 'Jane Smith']);
        Author::factory()->create(['name' => 'Mike Johnson']);

        $response = $this->getJson(route('api.v1.authors.index', ['search' => 'John']));

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.pagination.total')); // John Doe and Mike Johnson
        $authorsData = collect($response->json('data.list'));
        $this->assertTrue($authorsData->contains('name', 'John Doe'));
        $this->assertTrue($authorsData->contains('name', 'Mike Johnson'));
    }

    public function test_can_sort_authors_by_name_asc(): void
    {
        Author::factory()->create(['name' => 'Zoe Wilson']);
        Author::factory()->create(['name' => 'Alice Brown']);
        Author::factory()->create(['name' => 'Bob Smith']);

        $response = $this->getJson(route('api.v1.authors.index', ['column' => 'name', 'dir' => 'asc']));

        $response->assertStatus(200);
        $authors = $response->json('data.list');
        $this->assertEquals('Alice Brown', $authors[0]['name']);
        $this->assertEquals('Bob Smith', $authors[1]['name']);
        $this->assertEquals('Zoe Wilson', $authors[2]['name']);
    }

    public function test_can_sort_authors_by_name_desc(): void
    {
        Author::factory()->create(['name' => 'Alice Brown']);
        Author::factory()->create(['name' => 'Zoe Wilson']);
        Author::factory()->create(['name' => 'Bob Smith']);

        $response = $this->getJson(route('api.v1.authors.index', ['column' => 'name', 'dir' => 'desc']));

        $response->assertStatus(200);
        $authors = $response->json('data.list');
        $this->assertEquals('Zoe Wilson', $authors[0]['name']);
        $this->assertEquals('Bob Smith', $authors[1]['name']);
        $this->assertEquals('Alice Brown', $authors[2]['name']);
    }

    public function test_can_sort_authors_by_id(): void
    {
        $author1 = Author::factory()->create(['name' => 'Author 1']);
        $author2 = Author::factory()->create(['name' => 'Author 2']);
        $author3 = Author::factory()->create(['name' => 'Author 3']);

        $response = $this->getJson(route('api.v1.authors.index', ['column' => 'id', 'dir' => 'asc']));

        $response->assertStatus(200);
        $authors = $response->json('data.list');
        $this->assertEquals($author1->id, $authors[0]['id']);
        $this->assertEquals($author2->id, $authors[1]['id']);
        $this->assertEquals($author3->id, $authors[2]['id']);
    }

    public function test_can_paginate_authors(): void
    {
        Author::factory(25)->create();

        $response = $this->getJson(route('api.v1.authors.index', ['length' => 5]));

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('data.pagination.count'));
        $this->assertEquals(25, $response->json('data.pagination.total'));
        $this->assertEquals(5, $response->json('data.pagination.total_pages'));
    }

    public function test_can_combine_search_and_sort(): void
    {
        Author::factory()->create(['name' => 'John Smith']);
        Author::factory()->create(['name' => 'John Doe']);
        Author::factory()->create(['name' => 'Jane Williams']);

        $response = $this->getJson(route('api.v1.authors.index', [
            'search' => 'John',
            'column' => 'name',
            'dir' => 'asc',
        ]));

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.pagination.total'));
        $authors = $response->json('data.list');
        $this->assertEquals('John Doe', $authors[0]['name']);
        $this->assertEquals('John Smith', $authors[1]['name']);
    }

    public function test_returns_empty_list_when_no_authors(): void
    {
        $response = $this->getJson(route('api.v1.authors.index'));

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('data.pagination.total'));
        $this->assertEmpty($response->json('data.list'));
    }

    public function test_ignores_invalid_sort_columns(): void
    {
        Author::factory(3)->create();

        $response = $this->getJson(route('api.v1.authors.index', ['column' => 'invalid_column', 'dir' => 'asc']));

        $response->assertStatus(200);
        // Should fallback to default sorting (id desc)
        $authors = $response->json('data.list');
        $this->assertTrue($authors[0]['id'] > $authors[1]['id']);
    }

    public function test_ignores_invalid_sort_direction(): void
    {
        Author::factory(3)->create();

        $response = $this->getJson(route('api.v1.authors.index', ['column' => 'name', 'dir' => 'invalid_dir']));

        $response->assertStatus(200);
        // Should fallback to default sorting (id desc)
        $authors = $response->json('data.list');
        $this->assertTrue($authors[0]['id'] > $authors[1]['id']);
    }
}
