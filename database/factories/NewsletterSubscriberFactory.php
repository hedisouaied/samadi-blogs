<?php

namespace Database\Factories;

use App\Enums\SubscriberStatus;
use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsletterSubscriber>
 */
class NewsletterSubscriberFactory extends Factory
{
    protected $model = NewsletterSubscriber::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'status' => SubscriberStatus::Pending,
            'source' => fake()->randomElement(['website', 'footer', 'popup', 'import']),
            'subscribed_at' => null,
            'unsubscribed_at' => null,
            'consent_at' => now(),
            'consent_source' => 'website',
            'last_email_sent_at' => null,
        ];
    }

    public function subscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriberStatus::Subscribed,
            'subscribed_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function unsubscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriberStatus::Unsubscribed,
            'subscribed_at' => fake()->dateTimeBetween('-1 year', '-1 month'),
            'unsubscribed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function bounced(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriberStatus::Bounced,
        ]);
    }
}
