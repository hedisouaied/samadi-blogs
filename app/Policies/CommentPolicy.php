<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewComments);
    }

    public function view(User $user, Comment $comment): bool
    {
        return $user->hasPermission(Permission::ViewComments);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ModerateComments);
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->hasPermission(Permission::ModerateComments);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->hasPermission(Permission::DeleteComments);
    }

    public function restore(User $user, Comment $comment): bool
    {
        return $user->hasPermission(Permission::DeleteComments);
    }

    public function forceDelete(User $user, Comment $comment): bool
    {
        return $user->isSuperAdmin();
    }

    public function moderate(User $user, Comment $comment): bool
    {
        return $user->hasPermission(Permission::ModerateComments);
    }

    public function reply(User $user, Comment $comment): bool
    {
        return $user->hasPermission(Permission::ModerateComments);
    }
}
