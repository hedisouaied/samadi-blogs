<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\MenuItem;
use App\Models\User;

class MenuItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewMenus);
    }

    public function view(User $user, MenuItem $item): bool
    {
        return $user->hasPermission(Permission::ViewMenus);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ManageMenus);
    }

    public function update(User $user, MenuItem $item): bool
    {
        return $user->hasPermission(Permission::ManageMenus);
    }

    public function delete(User $user, MenuItem $item): bool
    {
        return $user->hasPermission(Permission::ManageMenus);
    }

    public function reorder(User $user): bool
    {
        return $user->hasPermission(Permission::ManageMenus);
    }
}
