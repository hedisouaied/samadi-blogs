<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paragraphs = fake()->paragraphs(6);

        return [
            'author_id' => User::factory(),
            'title' => rtrim(fake()->unique()->sentence(6), '.'),
            'excerpt' => fake()->paragraph(2),
            'content' => '<p>'.implode('</p><p>', $paragraphs).'</p>',
            'featured_image' => null,
            'featured_image_alt' => null,
            'status' => ArticleStatus::Draft,
            'is_featured' => false,
            'is_active' => true,
            'allow_comments' => true,
            'published_at' => null,
            'scheduled_at' => null,
            'seo_title' => null,
            'seo_description' => fake()->sentence(12),
            'canonical_url' => null,
            'seo_noindex' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ArticleStatus::Published,
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    public function pendingReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ArticleStatus::PendingReview,
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ArticleStatus::Scheduled,
            'scheduled_at' => fake()->dateTimeBetween('now', '+2 months'),
            'published_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ArticleStatus::Archived,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
