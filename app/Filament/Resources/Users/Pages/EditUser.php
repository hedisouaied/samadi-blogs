<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

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
        /** @var User|null $actor */
        $actor = Auth::user();

        if (array_key_exists('role', $data)
            && ($data['role'] ?? null) === UserRole::SuperAdmin->value
            && ! $actor?->hasPermission(Permission::ManageUserRoles)) {
            unset($data['role']);
        }

        return $data;
    }
}
