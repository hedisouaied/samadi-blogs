<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Enums\SubscriberStatus;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsletterSubscriber extends CreateRecord
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['status'] ?? null) === SubscriberStatus::Subscribed->value) {
            $data['subscribed_at'] ??= now();
        }

        return $data;
    }
}
