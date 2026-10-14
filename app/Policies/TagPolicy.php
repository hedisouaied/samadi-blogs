<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewTags);
    }

    public function view(User $user, Tag $tag): bool
    {
        return $user->hasPermission(Permission::ViewTags);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::CreateTags);
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->hasPermission(Permission::UpdateTags);
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->hasPermission(Permission::DeleteTags);
    }

    public function restore(User $user, Tag $tag): bool
    {
        return $user->hasPermission(Permission::DeleteTags);
    }

    public function forceDelete(User $user, Tag $tag): bool
    {
        return $user->isSuperAdmin();
    }
}
