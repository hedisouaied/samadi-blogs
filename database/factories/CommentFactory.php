<?php

namespace Database\Factories;

use App\Enums\CommentStatus;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'user_id' => null,
            'parent_id' => null,
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
            'content' => fake()->paragraph(),
            'status' => CommentStatus::Pending,
            'is_pinned' => false,
            'approved_at' => null,
            'approved_by' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommentStatus::Approved,
            'approved_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommentStatus::Rejected,
        ]);
    }

    public function spam(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommentStatus::Spam,
        ]);
    }
}
