<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\ArticleRevision;
use App\Models\User;

class ArticleRevisionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewArticles);
    }

    public function view(User $user, ArticleRevision $revision): bool
    {
        return $user->hasPermission(Permission::ViewArticles);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ArticleRevision $revision): bool
    {
        return false;
    }

    public function delete(User $user, ArticleRevision $revision): bool
    {
        return $user->isSuperAdmin();
    }
}
