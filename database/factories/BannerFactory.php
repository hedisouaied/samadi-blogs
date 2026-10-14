<?php

namespace Database\Factories;

use App\Enums\BannerPlacement;
use App\Enums\BannerStatus;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->unique()->sentence(4), '.'),
            'subtitle' => fake()->catchPhrase(),
            'description' => fake()->paragraph(),
            'image_path' => null,
            'mobile_image_path' => null,
            'alt_text' => fake()->sentence(4),
            'target_url' => fake()->url(),
            'button_text' => fake()->randomElement(['Learn more', 'Shop now', 'Read more', 'Get started']),
            'placement' => fake()->randomElement(BannerPlacement::cases()),
            'status' => BannerStatus::Draft,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 10),
            'starts_at' => null,
            'ends_at' => null,
            'open_in_new_tab' => false,
            'created_by' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BannerStatus::Published,
            'starts_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'ends_at' => fake()->dateTimeBetween('+1 month', '+6 months'),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BannerStatus::Archived,
            'is_active' => false,
        ]);
    }
}
