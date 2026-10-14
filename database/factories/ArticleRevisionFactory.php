<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleRevision>
 */
class ArticleRevisionFactory extends Factory
{
    protected $model = ArticleRevision::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->words(6, true), '.');
        $paragraphs = fake()->paragraphs(5);

        return [
            'article_id' => Article::factory(),
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'excerpt' => fake()->paragraph(2),
            'content' => '<p>'.implode('</p><p>', $paragraphs).'</p>',
            'status' => ArticleStatus::Draft,
            'note' => fake()->sentence(),
        ];
    }
}
