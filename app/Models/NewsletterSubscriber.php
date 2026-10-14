<?php

namespace App\Models;

use App\Enums\SubscriberStatus;
use Database\Factories\NewsletterSubscriberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'email',
    'name',
    'status',
    'source',
    'subscribed_at',
    'unsubscribed_at',
    'consent_at',
    'consent_source',
    'last_email_sent_at',
])]
class NewsletterSubscriber extends Model
{
    /** @use HasFactory<NewsletterSubscriberFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SubscriberStatus::class,
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'consent_at' => 'datetime',
            'last_email_sent_at' => 'datetime',
        ];
    }

    /**
     * Normalize emails to lowercase on assignment.
     */
    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['email'] = $value !== null ? strtolower(trim($value)) : null;
    }

    public function markSubscribed(?string $source = null): void
    {
        $this->status = SubscriberStatus::Subscribed;
        $this->subscribed_at ??= now();
        $this->unsubscribed_at = null;

        if ($source) {
            $this->source ??= $source;
        }
    }

    public function markUnsubscribed(): void
    {
        $this->status = SubscriberStatus::Unsubscribed;
        $this->unsubscribed_at = now();
    }

    /** @param Builder<NewsletterSubscriber> $query */
    public function scopeSubscribed(Builder $query): void
    {
        $query->where('status', SubscriberStatus::Subscribed->value);
    }

    /** @param Builder<NewsletterSubscriber> $query */
    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', [
            SubscriberStatus::Subscribed->value,
            SubscriberStatus::Pending->value,
        ]);
    }
}
