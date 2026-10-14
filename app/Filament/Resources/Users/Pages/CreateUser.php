<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

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
        /** @var User|null $actor */
        $actor = Auth::user();

        // Non super admins cannot create a Super Admin even by tampering with the request.
        if (($data['role'] ?? null) === UserRole::SuperAdmin->value
            && ! $actor?->hasPermission(Permission::ManageUserRoles)) {
            $data['role'] = UserRole::Admin->value;
        }

        return $data;
    }
}
