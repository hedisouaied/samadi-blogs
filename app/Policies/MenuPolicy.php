<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Menu;
use App\Models\User;

class MenuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewMenus);
    }

    public function view(User $user, Menu $menu): bool
    {
        return $user->hasPermission(Permission::ViewMenus);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ManageMenus);
    }

    public function update(User $user, Menu $menu): bool
    {
        return $user->hasPermission(Permission::ManageMenus);
    }

    public function delete(User $user, Menu $menu): bool
    {
        return $user->hasPermission(Permission::ManageMenus);
    }
}
