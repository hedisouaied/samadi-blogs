<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Enums\Permission;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Models\NewsletterSubscriber;
use App\Support\Csv;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListNewsletterSubscribers extends ListRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::ExportSubscribers) ?? false)
                ->action(function () {
                    $rows = $this->getTableQueryForExport()
                        ->cursor()
                        ->map(fn (NewsletterSubscriber $subscriber): array => [
                            $subscriber->email,
                            $subscriber->name,
                            $subscriber->status->value,
                            $subscriber->source,
                            optional($subscriber->consent_at)->toDateTimeString(),
                            optional($subscriber->subscribed_at)->toDateTimeString(),
                            optional($subscriber->unsubscribed_at)->toDateTimeString(),
                            optional($subscriber->created_at)->toDateTimeString(),
                        ]);

                    return Csv::stream(
                        'newsletter-subscribers-'.now()->format('Y-m-d').'.csv',
                        ['Email', 'Name', 'Status', 'Source', 'Consent at', 'Subscribed at', 'Unsubscribed at', 'Created at'],
                        $rows,
                    );
                }),
            CreateAction::make()->icon('heroicon-o-plus'),
        ];
    }
}
