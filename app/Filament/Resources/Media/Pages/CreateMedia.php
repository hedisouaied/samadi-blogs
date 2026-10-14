<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use App\Services\MediaService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

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
        $data['uploaded_by'] = Auth::id();
        $data['display_name'] ??= filled($data['original_filename'] ?? null)
            ? pathinfo((string) $data['original_filename'], PATHINFO_FILENAME)
            : null;

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Media $media */
        $media = $this->getRecord();

        app(MediaService::class)->syncMetadata($media);
    }
}
