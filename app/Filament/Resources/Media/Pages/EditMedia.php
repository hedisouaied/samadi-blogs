<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use App\Services\MediaService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->action(function (Media $record): void {
                    app(MediaService::class)->deleteFile($record);
                    $record->delete();

                    Notification::make()->title('Media deleted')->success()->send();

                    $this->redirect(MediaResource::getUrl('index'));
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        /** @var Media $media */
        $media = $this->getRecord();

        app(MediaService::class)->syncMetadata($media);
    }
}
