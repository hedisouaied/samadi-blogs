<?php

namespace Database\Factories;

use App\Enums\MenuItemType;
use App\Models\Article;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'label' => rtrim(fake()->words(2, true), '.'),
            'type' => MenuItemType::CustomUrl,
            'article_id' => null,
            'category_id' => null,
            'url' => '#'.fake()->slug(2),
            'sort_order' => fake()->numberBetween(0, 10),
            'open_in_new_tab' => false,
            'is_active' => true,
        ];
    }

    public function forCategory(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MenuItemType::Category,
            'category_id' => Category::factory(),
            'url' => null,
        ]);
    }

    public function forArticle(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MenuItemType::Article,
            'article_id' => Article::factory(),
            'url' => null,
        ]);
    }
}
