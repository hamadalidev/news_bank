<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_categories_list(): void
    {
        Category::factory(8)->create();

        $response = $this->getJson(route('api.v1.categories.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'list' => [
                        '*' => [
                            'id',
                            'name',
                        ]
                    ],
                    'pagination' => [
                        'total',
                        'count',
                        'per_page',
                        'current_page',
                        'total_pages'
                    ]
                ]
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals(8, $response->json('data.pagination.count'));
        $this->assertEquals(8, $response->json('data.pagination.total'));
    }

    public function test_can_search_categories(): void
    {
        Category::factory()->create(['name' => 'Technology']);
        Category::factory()->create(['name' => 'Sports Technology']);
        Category::factory()->create(['name' => 'Business']);
        Category::factory()->create(['name' => 'Entertainment']);

        $response = $this->getJson(route('api.v1.categories.index', ['search' => 'Technology']));

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.pagination.total')); // Technology and Sports Technology
        $categoriesData = collect($response->json('data.list'));
        $this->assertTrue($categoriesData->contains('name', 'Technology'));
        $this->assertTrue($categoriesData->contains('name', 'Sports Technology'));
    }

    public function test_can_sort_categories_by_name_asc(): void
    {
        Category::factory()->create(['name' => 'Technology']);
        Category::factory()->create(['name' => 'Business']);
        Category::factory()->create(['name' => 'Sports']);

        $response = $this->getJson(route('api.v1.categories.index', ['column' => 'name', 'dir' => 'asc']));

        $response->assertStatus(200);
        $categories = $response->json('data.list');
        $this->assertEquals('Business', $categories[0]['name']);
        $this->assertEquals('Sports', $categories[1]['name']);
        $this->assertEquals('Technology', $categories[2]['name']);
    }

    public function test_can_sort_categories_by_name_desc(): void
    {
        Category::factory()->create(['name' => 'Business']);
        Category::factory()->create(['name' => 'Technology']);
        Category::factory()->create(['name' => 'Sports']);

        $response = $this->getJson(route('api.v1.categories.index', ['column' => 'name', 'dir' => 'desc']));

        $response->assertStatus(200);
        $categories = $response->json('data.list');
        $this->assertEquals('Technology', $categories[0]['name']);
        $this->assertEquals('Sports', $categories[1]['name']);
        $this->assertEquals('Business', $categories[2]['name']);
    }

    public function test_can_sort_categories_by_id(): void
    {
        $category1 = Category::factory()->create(['name' => 'Category 1']);
        $category2 = Category::factory()->create(['name' => 'Category 2']);
        $category3 = Category::factory()->create(['name' => 'Category 3']);

        $response = $this->getJson(route('api.v1.categories.index', ['column' => 'id', 'dir' => 'asc']));

        $response->assertStatus(200);
        $categories = $response->json('data.list');
        $this->assertEquals($category1->id, $categories[0]['id']);
        $this->assertEquals($category2->id, $categories[1]['id']);
        $this->assertEquals($category3->id, $categories[2]['id']);
    }

    public function test_can_paginate_categories(): void
    {
        // Create categories with unique names to avoid factory collision
        for ($i = 1; $i <= 15; $i++) {
            Category::factory()->create(['name' => "Category $i"]);
        }

        $response = $this->getJson(route('api.v1.categories.index', ['length' => 5]));

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('data.pagination.count'));
        $this->assertEquals(15, $response->json('data.pagination.total'));
        $this->assertEquals(3, $response->json('data.pagination.total_pages'));
    }

    public function test_can_combine_search_and_sort(): void
    {
        Category::factory()->create(['name' => 'Sports News']);
        Category::factory()->create(['name' => 'Business News']);
        Category::factory()->create(['name' => 'Entertainment']);

        $response = $this->getJson(route('api.v1.categories.index', [
            'search' => 'News',
            'column' => 'name',
            'dir' => 'asc'
        ]));

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.pagination.total'));
        $categories = $response->json('data.list');
        $this->assertEquals('Business News', $categories[0]['name']);
        $this->assertEquals('Sports News', $categories[1]['name']);
    }

    public function test_returns_empty_list_when_no_categories(): void
    {
        $response = $this->getJson(route('api.v1.categories.index'));

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('data.pagination.total'));
        $this->assertEmpty($response->json('data.list'));
    }

    public function test_ignores_invalid_sort_columns(): void
    {
        Category::factory(3)->create();

        $response = $this->getJson(route('api.v1.categories.index', ['column' => 'invalid_column', 'dir' => 'asc']));

        $response->assertStatus(200);
        // Should fallback to default sorting (id desc)
        $categories = $response->json('data.list');
        $this->assertTrue($categories[0]['id'] > $categories[1]['id']);
    }

    public function test_ignores_invalid_sort_direction(): void
    {
        Category::factory(3)->create();

        $response = $this->getJson(route('api.v1.categories.index', ['column' => 'name', 'dir' => 'invalid_dir']));

        $response->assertStatus(200);
        // Should fallback to default sorting (id desc)
        $categories = $response->json('data.list');
        $this->assertTrue($categories[0]['id'] > $categories[1]['id']);
    }

    public function test_case_insensitive_search(): void
    {
        Category::factory()->create(['name' => 'Technology']);
        Category::factory()->create(['name' => 'Business']);

        $response = $this->getJson(route('api.v1.categories.index', ['search' => 'technology']));

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.pagination.total'));
        $this->assertEquals('Technology', $response->json('data.list.0.name'));
    }
}
