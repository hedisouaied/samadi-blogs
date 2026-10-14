<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewUsers);
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermission(Permission::ViewUsers) || $user->is($model);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::CreateUsers);
    }

    public function update(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return true;
        }

        return $user->hasPermission(Permission::UpdateUsers);
    }

    public function delete(User $user, User $model): bool
    {
        // Never allow deleting your own account or a Super Admin unless you are one.
        if ($user->is($model)) {
            return false;
        }

        if ($model->role === UserRole::SuperAdmin && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->hasPermission(Permission::DeleteUsers);
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasPermission(Permission::DeleteUsers);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->isSuperAdmin() && ! $user->is($model);
    }

    /**
     * Whether the actor may change a user's role.
     */
    public function updateRole(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return false;
        }

        return $user->hasPermission(Permission::ManageUserRoles);
    }
}
