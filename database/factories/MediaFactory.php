<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->slug(3);

        return [
            'path' => 'media/'.$name.'.jpg',
            'original_filename' => $name.'.jpg',
            'display_name' => str($name)->headline()->toString(),
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(20_000, 1_500_000),
            'width' => 1200,
            'height' => 800,
            'alt_text' => fake()->sentence(5),
            'caption' => fake()->optional()->sentence(),
            'collection' => 'default',
            'uploaded_by' => User::factory(),
            'meta' => null,
        ];
    }

    public function document(): static
    {
        return $this->state(function (array $attributes) {
            $name = fake()->unique()->slug(3);

            return [
                'path' => 'media/'.$name.'.pdf',
                'original_filename' => $name.'.pdf',
                'mime_type' => 'application/pdf',
                'width' => null,
                'height' => null,
            ];
        });
    }
}
