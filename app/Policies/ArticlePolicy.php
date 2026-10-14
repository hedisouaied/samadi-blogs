<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewArticles);
    }

    public function view(User $user, Article $article): bool
    {
        return $user->hasPermission(Permission::ViewArticles);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::CreateArticles);
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->hasPermission(Permission::UpdateAnyArticle)) {
            return true;
        }

        return $user->hasPermission(Permission::UpdateOwnArticle)
            && $article->author_id === $user->getKey();
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->hasPermission(Permission::DeleteArticles);
    }

    public function restore(User $user, Article $article): bool
    {
        return $user->hasPermission(Permission::DeleteArticles);
    }

    public function forceDelete(User $user, Article $article): bool
    {
        return $user->isSuperAdmin();
    }

    public function publish(User $user, Article $article): bool
    {
        return $user->hasPermission(Permission::PublishArticles);
    }

    public function review(User $user, Article $article): bool
    {
        return $user->hasPermission(Permission::ReviewArticles);
    }

    public function duplicate(User $user, Article $article): bool
    {
        return $user->hasPermission(Permission::CreateArticles)
            && $user->hasPermission(Permission::ViewArticles);
    }
}
