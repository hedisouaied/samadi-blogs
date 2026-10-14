<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewMedia);
    }

    public function view(User $user, Media $media): bool
    {
        return $user->hasPermission(Permission::ViewMedia);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::CreateMedia);
    }

    public function update(User $user, Media $media): bool
    {
        return $user->hasPermission(Permission::CreateMedia);
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->hasPermission(Permission::DeleteMedia);
    }
}
