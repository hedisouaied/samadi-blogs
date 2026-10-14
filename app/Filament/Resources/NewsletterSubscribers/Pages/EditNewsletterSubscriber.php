<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Enums\SubscriberStatus;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditNewsletterSubscriber extends EditRecord
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === SubscriberStatus::Subscribed->value) {
            $data['subscribed_at'] ??= now();
        }

        return $data;
    }
}
