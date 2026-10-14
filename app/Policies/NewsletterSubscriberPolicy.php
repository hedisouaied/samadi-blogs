<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\NewsletterSubscriber;
use App\Models\User;

class NewsletterSubscriberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewSubscribers);
    }

    public function view(User $user, NewsletterSubscriber $subscriber): bool
    {
        return $user->hasPermission(Permission::ViewSubscribers);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::CreateSubscribers);
    }

    public function update(User $user, NewsletterSubscriber $subscriber): bool
    {
        return $user->hasPermission(Permission::UpdateSubscribers);
    }

    public function delete(User $user, NewsletterSubscriber $subscriber): bool
    {
        return $user->hasPermission(Permission::DeleteSubscribers);
    }

    public function export(User $user): bool
    {
        return $user->hasPermission(Permission::ExportSubscribers);
    }
}
