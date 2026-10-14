<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $action = fake()->randomElement(['created', 'updated', 'deleted', 'published', 'approved', 'login']);

        return [
            'user_id' => User::factory(),
            'action' => $action,
            'subject_type' => null,
            'subject_id' => null,
            'description' => fake()->sentence(),
            'properties' => null,
            'ip_address' => fake()->ipv4(),
        ];
    }
}
