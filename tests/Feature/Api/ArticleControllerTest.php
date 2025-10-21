<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\NewsSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_articles_list(): void
    {
        $source = NewsSource::factory()->enabled()->newsDataIO()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();

        Article::factory(15)->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        $response = $this->getJson(route('api.v1.articles.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'list' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'url',
                            'image_url',
                            'published_at',
                            'source' => ['id', 'name'],
                            'category' => ['id', 'name'],
                            'author' => ['id', 'name'],
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
        $this->assertEquals(15, $response->json('data.pagination.total'));
    }

    public function test_can_search_articles(): void
    {
        $source = NewsSource::factory()->enabled()->newsDataIO()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();

        Article::factory()->create([
            'title' => 'Laravel Framework Tutorial',
            'description' => 'Learn Laravel development',
            'source_id' => $source->id,
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        Article::factory()->create([
            'title' => 'Vue.js Components',
            'description' => 'Building Vue components',
            'source_id' => $source->id,
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        $response = $this->getJson(route('api.v1.articles.index', ['search' => 'Laravel']));

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.pagination.total'));
        $this->assertStringContainsString('Laravel', $response->json('data.list.0.title'));
    }

    public function test_can_filter_articles_by_category(): void
    {
        $source = NewsSource::factory()->enabled()->newsDataIO()->create();
        $techCategory = Category::factory()->create(['name' => 'Technology']);
        $sportsCategory = Category::factory()->create(['name' => 'Sports']);
        $author = Author::factory()->create();

        Article::factory(3)->create([
            'source_id' => $source->id,
            'category_id' => $techCategory->id,
            'author_id' => $author->id,
        ]);

        Article::factory(2)->create([
            'source_id' => $source->id,
            'category_id' => $sportsCategory->id,
            'author_id' => $author->id,
        ]);

        $response = $this->getJson(route('api.v1.articles.index', ['category' => $techCategory->id]));

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('data.pagination.total'));
    }

    public function test_can_filter_articles_by_author(): void
    {
        $source = NewsSource::factory()->enabled()->newsDataIO()->create();
        $category = Category::factory()->create();
        $author1 = Author::factory()->create(['name' => 'John Doe']);
        $author2 = Author::factory()->create(['name' => 'Jane Smith']);

        Article::factory(4)->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
            'author_id' => $author1->id,
        ]);

        Article::factory(2)->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
            'author_id' => $author2->id,
        ]);

        $response = $this->getJson(route('api.v1.articles.index', ['author' => $author1->id]));

        $response->assertStatus(200);
        $this->assertEquals(4, $response->json('data.pagination.total'));
    }

    public function test_can_filter_articles_by_source(): void
    {
        $source1 = NewsSource::factory()->enabled()->newsDataIO()->create(['name' => 'TechNews']);
        $source2 = NewsSource::factory()->enabled()->guardian()->create(['name' => 'SportsNews']);
        $category = Category::factory()->create();
        $author = Author::factory()->create();

        Article::factory(5)->create([
            'source_id' => $source1->id,
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        Article::factory(3)->create([
            'source_id' => $source2->id,
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        $response = $this->getJson(route('api.v1.articles.index', ['source' => $source1->id]));

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('data.pagination.total'));
    }

    public function test_can_sort_articles(): void
    {
        $source = NewsSource::factory()->enabled()->newsDataIO()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();

        Article::factory()->create([
            'title' => 'Article A',
            'source_id' => $source->id,
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        Article::factory()->create([
            'title' => 'Article B',
            'source_id' => $source->id,
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        $response = $this->getJson(route('api.v1.articles.index', ['column' => 'title', 'dir' => 'asc']));

        $response->assertStatus(200);
        $this->assertEquals('Article A', $response->json('data.list.0.title'));
        $this->assertEquals('Article B', $response->json('data.list.1.title'));
    }

    public function test_can_paginate_articles(): void
    {
        $source = NewsSource::factory()->enabled()->newsDataIO()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();

        Article::factory(25)->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        $response = $this->getJson(route('api.v1.articles.index', ['length' => 5]));

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('data.pagination.count'));
        $this->assertEquals(25, $response->json('data.pagination.total'));
        $this->assertEquals(5, $response->json('data.pagination.total_pages'));
    }

    public function test_can_combine_filters(): void
    {
        $source = NewsSource::factory()->enabled()->newsDataIO()->create();
        $techCategory = Category::factory()->create(['name' => 'Technology']);
        $author = Author::factory()->create(['name' => 'Tech Writer']);

        Article::factory()->create([
            'title' => 'Laravel News Update',
            'source_id' => $source->id,
            'category_id' => $techCategory->id,
            'author_id' => $author->id,
        ]);

        Article::factory()->create([
            'title' => 'Vue.js Framework',
            'source_id' => $source->id,
            'category_id' => $techCategory->id,
            'author_id' => $author->id,
        ]);

        $response = $this->getJson(route('api.v1.articles.index', [
            'search' => 'Laravel',
            'category' => $techCategory->id,
            'author' => $author->id,
        ]));

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.pagination.total'));
        $this->assertStringContainsString('Laravel', $response->json('data.list.0.title'));
    }

    public function test_returns_empty_list_when_no_articles(): void
    {
        $response = $this->getJson(route('api.v1.articles.index'));

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('data.pagination.total'));
        $this->assertEmpty($response->json('data.list'));
    }
}
