<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(12),
            'image_path' => null,
            'parent_id' => null,
            'color' => fake()->randomElement(['#6366f1', '#0ea5e9', '#22c55e', '#f97316', '#ec4899']),
            'icon' => fake()->randomElement(['heroicon-o-folder', 'heroicon-o-bookmark', 'heroicon-o-tag', 'heroicon-o-sparkles']),
            'sort_order' => fake()->numberBetween(0, 25),
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
            'show_in_menu' => true,
            'seo_title' => null,
            'seo_description' => fake()->sentence(10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
