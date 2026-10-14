<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

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

    protected function beforeSave(): void
    {
        $parentId = $this->data['parent_id'] ?? null;

        if ($this->getRecord()->wouldCreateCycle($parentId ? (int) $parentId : null)) {
            throw ValidationException::withMessages([
                'data.parent_id' => 'A category cannot be its own ancestor or descendant.',
            ]);
        }
    }
}
