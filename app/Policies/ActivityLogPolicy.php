<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewActivity);
    }

    public function view(User $user, ActivityLog $log): bool
    {
        return $user->hasPermission(Permission::ViewActivity);
    }

    public function delete(User $user, ActivityLog $log): bool
    {
        return $user->hasPermission(Permission::DeleteActivity);
    }
}
