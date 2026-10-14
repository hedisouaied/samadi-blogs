<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewCategories);
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermission(Permission::ViewCategories);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::CreateCategories);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermission(Permission::UpdateCategories);
    }

    public function delete(User $user, Category $category): bool
    {
        // Categories that still hold articles must be emptied first.
        if ($category->articles()->exists()) {
            return false;
        }

        return $user->hasPermission(Permission::DeleteCategories);
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->hasPermission(Permission::DeleteCategories);
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $user->isSuperAdmin() && ! $category->articles()->exists();
    }
}
