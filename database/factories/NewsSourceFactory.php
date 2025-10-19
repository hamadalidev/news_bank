<?php

namespace Database\Factories;

use App\Enums\NewsSourceEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsSource>
 */
class NewsSourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_enum' => fake()->randomElement(NewsSourceEnum::cases()),
            'name' => fake()->unique()->company(),
            'api_key' => fake()->uuid(),
            'status' => fake()->randomElement(['enabled', 'disabled']),
            'base_url' => fake()->url(),
            'rate_limit_per_hour' => fake()->numberBetween(100, 1000),
            'last_fetched_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function enabled(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'enabled',
        ]);
    }

    public function disabled(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disabled',
        ]);
    }

    public function newsDataIO(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'source_enum' => NewsSourceEnum::NEWSDATA_IO,
            'name' => 'NewsData.io',
        ]);
    }

    public function guardian(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'source_enum' => NewsSourceEnum::GUARDIAN,
            'name' => 'The Guardian',
        ]);
    }

    public function newYorkTimes(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'source_enum' => NewsSourceEnum::NEW_YORK_TIMES,
            'name' => 'New York Times',
        ]);
    }

    public function newsAPI(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'source_enum' => NewsSourceEnum::NEWSAPI,
            'name' => 'NewsAPI',
        ]);
    }

    public function withUniqueEnum(): Factory
    {
        return $this->sequence(
            ['source_enum' => NewsSourceEnum::NEWSDATA_IO, 'name' => 'NewsData.io'],
            ['source_enum' => NewsSourceEnum::GUARDIAN, 'name' => 'The Guardian'],
            ['source_enum' => NewsSourceEnum::NEW_YORK_TIMES, 'name' => 'New York Times'],
            ['source_enum' => NewsSourceEnum::NEWSAPI, 'name' => 'NewsAPI']
        );
    }
}
