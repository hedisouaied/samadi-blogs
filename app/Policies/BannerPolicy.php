<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Banner;
use App\Models\User;

class BannerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewBanners);
    }

    public function view(User $user, Banner $banner): bool
    {
        return $user->hasPermission(Permission::ViewBanners);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::CreateBanners);
    }

    public function update(User $user, Banner $banner): bool
    {
        return $user->hasPermission(Permission::UpdateBanners);
    }

    public function delete(User $user, Banner $banner): bool
    {
        return $user->hasPermission(Permission::DeleteBanners);
    }

    public function restore(User $user, Banner $banner): bool
    {
        return $user->hasPermission(Permission::DeleteBanners);
    }

    public function forceDelete(User $user, Banner $banner): bool
    {
        return $user->isSuperAdmin();
    }
}
