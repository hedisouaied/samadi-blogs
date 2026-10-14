<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'avatar_path' => null,
            'bio' => fake()->paragraph(),
            'job_title' => fake()->jobTitle(),
            'role' => UserRole::Author,
            'status' => UserStatus::Active,
            'last_login_at' => fake()->optional()->dateTimeBetween('-30 days'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withRole(UserRole $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => $role,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->withRole(UserRole::SuperAdmin);
    }

    public function admin(): static
    {
        return $this->withRole(UserRole::Admin);
    }

    public function editor(): static
    {
        return $this->withRole(UserRole::Editor);
    }

    public function author(): static
    {
        return $this->withRole(UserRole::Author);
    }

    public function moderator(): static
    {
        return $this->withRole(UserRole::Moderator);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Inactive,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Suspended,
        ]);
    }
}
