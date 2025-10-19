<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Category;
use App\Models\NewsSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->uuid(),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(3),
            'url' => fake()->url(),
            'image_url' => fake()->optional()->imageUrl(640, 480, 'news'),
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'language' => fake()->randomElement(['en', 'es', 'fr', 'de']),
            'country' => fake()->randomElement(['us', 'uk', 'ca', 'au']),
            'source_id' => NewsSource::factory(),
            'category_id' => Category::factory(),
            'author_id' => Author::factory(),
        ];
    }

    public function withSource(NewsSource $source): Factory
    {
        return $this->state(fn (array $attributes) => [
            'source_id' => $source->id,
        ]);
    }

    public function withCategory(Category $category): Factory
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    public function withAuthor(Author $author): Factory
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => $author->id,
        ]);
    }
}
