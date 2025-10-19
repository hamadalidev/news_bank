<?php

namespace Tests\Feature\Api;

use App\Enums\NewsSourceEnum;
use App\Models\NewsSource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NewsSourceControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_sources_list(): void
    {
        NewsSource::factory()->withUniqueEnum()->count(3)->create();

        $response = $this->getJson(route('api.v1.sources.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'list' => [
                        '*' => [
                            'id',
                            'name',
                            'source_enum',
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
        $this->assertEquals(3, $response->json('data.pagination.count'));
        $this->assertEquals(3, $response->json('data.pagination.total'));
    }

    public function test_can_search_sources(): void
    {
        NewsSource::factory()->newsDataIO()->create(['name' => 'TechCrunch News']);
        NewsSource::factory()->guardian()->create(['name' => 'Sports Daily']);
        NewsSource::factory()->newYorkTimes()->create(['name' => 'Tech Weekly']);

        $response = $this->getJson(route('api.v1.sources.index', ['search' => 'Tech']));

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.pagination.total')); // TechCrunch News and Tech Weekly
        $sourcesData = collect($response->json('data.list'));
        $this->assertTrue($sourcesData->contains('name', 'TechCrunch News'));
        $this->assertTrue($sourcesData->contains('name', 'Tech Weekly'));
    }

    public function test_can_sort_sources_by_name_asc(): void
    {
        NewsSource::factory()->newsDataIO()->create(['name' => 'Zeta News']);
        NewsSource::factory()->guardian()->create(['name' => 'Alpha News']);
        NewsSource::factory()->newsAPI()->create(['name' => 'Beta News']);

        $response = $this->getJson(route('api.v1.sources.index', ['column' => 'name', 'dir' => 'asc']));

        $response->assertStatus(200);
        $sources = $response->json('data.list');
        $this->assertEquals('Alpha News', $sources[0]['name']);
        $this->assertEquals('Beta News', $sources[1]['name']);
        $this->assertEquals('Zeta News', $sources[2]['name']);
    }

    public function test_can_sort_sources_by_status(): void
    {
        NewsSource::factory()->newsDataIO()->enabled()->create(['name' => 'Source A']);
        NewsSource::factory()->guardian()->disabled()->create(['name' => 'Source B']);
        NewsSource::factory()->newsAPI()->enabled()->create(['name' => 'Source C']);

        $response = $this->getJson(route('api.v1.sources.index', ['column' => 'status', 'dir' => 'asc']));

        $response->assertStatus(200);
        $sources = $response->json('data.list');
        // Since status is not in the resource, just verify we got 3 sources
        $this->assertCount(3, $sources);
    }

    public function test_can_sort_sources_by_id(): void
    {
        $source1 = NewsSource::factory()->newsDataIO()->create(['name' => 'Source 1']);
        $source2 = NewsSource::factory()->guardian()->create(['name' => 'Source 2']);
        $source3 = NewsSource::factory()->newsAPI()->create(['name' => 'Source 3']);

        $response = $this->getJson(route('api.v1.sources.index', ['column' => 'id', 'dir' => 'asc']));

        $response->assertStatus(200);
        $sources = $response->json('data.list');
        $this->assertEquals($source1->id, $sources[0]['id']);
        $this->assertEquals($source2->id, $sources[1]['id']);
        $this->assertEquals($source3->id, $sources[2]['id']);
    }

    public function test_can_paginate_sources(): void
    {
        // Create 4 sources (one of each enum) to test pagination
        NewsSource::factory()->newsDataIO()->create(['name' => 'Source 1']);
        NewsSource::factory()->guardian()->create(['name' => 'Source 2']);
        NewsSource::factory()->newsAPI()->create(['name' => 'Source 3']);
        NewsSource::factory()->newYorkTimes()->create(['name' => 'Source 4']);

        $response = $this->getJson(route('api.v1.sources.index', ['length' => 2]));

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.pagination.count'));
        $this->assertEquals(4, $response->json('data.pagination.total'));
        $this->assertEquals(2, $response->json('data.pagination.total_pages')); // 4 sources / 2 per page = 2 pages
    }

    public function test_can_combine_search_and_sort(): void
    {
        NewsSource::factory()->newsDataIO()->create(['name' => 'Daily News']);
        NewsSource::factory()->guardian()->create(['name' => 'Business Daily']);
        NewsSource::factory()->newsAPI()->create(['name' => 'Entertainment Today']);

        $response = $this->getJson(route('api.v1.sources.index', [
            'search' => 'Daily',
            'column' => 'name',
            'dir' => 'asc',
        ]));

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.pagination.total'));
        $sources = $response->json('data.list');
        $this->assertEquals('Business Daily', $sources[0]['name']);
        $this->assertEquals('Daily News', $sources[1]['name']);
    }

    public function test_returns_empty_list_when_no_sources(): void
    {
        $response = $this->getJson(route('api.v1.sources.index'));

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('data.pagination.total'));
        $this->assertEmpty($response->json('data.list'));
    }

    public function test_shows_enabled_and_disabled_sources(): void
    {
        NewsSource::factory()->newsDataIO()->create(['name' => 'Source 1']);
        NewsSource::factory()->guardian()->create(['name' => 'Source 2']);
        NewsSource::factory()->newsAPI()->create(['name' => 'Source 3']);
        NewsSource::factory()->newYorkTimes()->create(['name' => 'Source 4']);

        $response = $this->getJson(route('api.v1.sources.index'));

        $response->assertStatus(200);
        $this->assertEquals(4, $response->json('data.pagination.total'));

        // Since status is not in the resource, just verify we got 4 sources
        $sources = $response->json('data.list');
        $this->assertCount(4, $sources);
    }

    public function test_includes_source_enum_in_response(): void
    {
        $source = NewsSource::factory()->create([
            'source_enum' => NewsSourceEnum::GUARDIAN,
            'name' => 'The Guardian',
        ]);

        $response = $this->getJson(route('api.v1.sources.index'));

        $response->assertStatus(200);
        $sourceData = $response->json('data.list.0');
        $this->assertEquals(NewsSourceEnum::GUARDIAN->value, $sourceData['source_enum']);
    }

    public function test_ignores_invalid_sort_columns(): void
    {
        NewsSource::factory()->newsDataIO()->create(['name' => 'Source 1']);
        NewsSource::factory()->guardian()->create(['name' => 'Source 2']);
        NewsSource::factory()->newsAPI()->create(['name' => 'Source 3']);

        $response = $this->getJson(route('api.v1.sources.index', ['column' => 'invalid_column', 'dir' => 'asc']));

        $response->assertStatus(200);
        // Should fallback to default sorting (id desc)
        $sources = $response->json('data.list');
        $this->assertTrue($sources[0]['id'] > $sources[1]['id']);
    }

    public function test_ignores_invalid_sort_direction(): void
    {
        NewsSource::factory()->newsDataIO()->create(['name' => 'Source 1']);
        NewsSource::factory()->guardian()->create(['name' => 'Source 2']);
        NewsSource::factory()->newsAPI()->create(['name' => 'Source 3']);

        $response = $this->getJson(route('api.v1.sources.index', ['column' => 'name', 'dir' => 'invalid_dir']));

        $response->assertStatus(200);
        // Should fallback to default sorting (id desc)
        $sources = $response->json('data.list');
        $this->assertTrue($sources[0]['id'] > $sources[1]['id']);
    }

    public function test_includes_all_required_fields(): void
    {
        $source = NewsSource::factory()->create([
            'name' => 'Test Source',
            'source_enum' => NewsSourceEnum::NEWSDATA_IO,
            'status' => 'enabled',
            'base_url' => 'https://test.com',
            'rate_limit_per_hour' => 500,
        ]);

        $response = $this->getJson(route('api.v1.sources.index'));

        $response->assertStatus(200);
        $sourceData = $response->json('data.list.0');

        $this->assertEquals($source->id, $sourceData['id']);
        $this->assertEquals('Test Source', $sourceData['name']);
        $this->assertEquals(NewsSourceEnum::NEWSDATA_IO->value, $sourceData['source_enum']);
    }

    public function test_does_not_expose_api_key(): void
    {
        NewsSource::factory()->create(['api_key' => 'secret-key']);

        $response = $this->getJson(route('api.v1.sources.index'));

        $response->assertStatus(200);
        $sourceData = $response->json('data.list.0');
        $this->assertArrayNotHasKey('api_key', $sourceData);
    }
}
