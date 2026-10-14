<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Editor = 'editor';
    case Author = 'author';
    case Moderator = 'moderator';

    public function getLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Editor => 'Editor',
            self::Author => 'Author',
            self::Moderator => 'Moderator',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Full unrestricted access to every part of the back office.',
            self::Admin => 'Full access to content and administration, with limited system-level control.',
            self::Editor => 'Manage and publish all content, moderate comments and maintain taxonomy.',
            self::Author => 'Create and manage their own articles and submit them for review.',
            self::Moderator => 'Moderate and manage comments only.',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::Admin => 'warning',
            self::Editor => 'info',
            self::Author => 'success',
            self::Moderator => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::SuperAdmin => 'heroicon-o-shield-check',
            self::Admin => 'heroicon-o-cog-6-tooth',
            self::Editor => 'heroicon-o-pencil-square',
            self::Author => 'heroicon-o-pencil',
            self::Moderator => 'heroicon-o-chat-bubble-left-right',
        };
    }

    /**
     * Permissions granted to this role.
     *
     * @return array<Permission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SuperAdmin => Permission::cases(),
            self::Admin => [
                Permission::AccessAdminPanel,
                Permission::ViewUsers, Permission::CreateUsers, Permission::UpdateUsers, Permission::DeleteUsers,
                Permission::ViewArticles, Permission::CreateArticles, Permission::UpdateAnyArticle, Permission::DeleteArticles, Permission::PublishArticles, Permission::ReviewArticles,
                Permission::ViewCategories, Permission::CreateCategories, Permission::UpdateCategories, Permission::DeleteCategories,
                Permission::ViewTags, Permission::CreateTags, Permission::UpdateTags, Permission::DeleteTags,
                Permission::ViewComments, Permission::ModerateComments, Permission::DeleteComments,
                Permission::ViewBanners, Permission::CreateBanners, Permission::UpdateBanners, Permission::DeleteBanners,
                Permission::ViewSubscribers, Permission::CreateSubscribers, Permission::UpdateSubscribers, Permission::DeleteSubscribers, Permission::ExportSubscribers,
                Permission::ViewMedia, Permission::CreateMedia, Permission::DeleteMedia,
                Permission::ViewMenus, Permission::ManageMenus,
                Permission::ViewSettings, Permission::ManageSettings,
                Permission::ViewActivity,
            ],
            self::Editor => [
                Permission::AccessAdminPanel,
                Permission::ViewUsers,
                Permission::ViewArticles, Permission::CreateArticles, Permission::UpdateAnyArticle, Permission::DeleteArticles, Permission::PublishArticles, Permission::ReviewArticles,
                Permission::ViewCategories, Permission::CreateCategories, Permission::UpdateCategories, Permission::DeleteCategories,
                Permission::ViewTags, Permission::CreateTags, Permission::UpdateTags, Permission::DeleteTags,
                Permission::ViewComments, Permission::ModerateComments, Permission::DeleteComments,
                Permission::ViewBanners, Permission::CreateBanners, Permission::UpdateBanners, Permission::DeleteBanners,
                Permission::ViewSubscribers, Permission::ExportSubscribers,
                Permission::ViewMedia, Permission::CreateMedia, Permission::DeleteMedia,
                Permission::ViewMenus, Permission::ManageMenus,
                Permission::ViewActivity,
            ],
            self::Author => [
                Permission::AccessAdminPanel,
                Permission::ViewArticles, Permission::CreateArticles, Permission::UpdateOwnArticle,
                Permission::ViewCategories, Permission::ViewTags,
                Permission::ViewComments,
                Permission::ViewMedia, Permission::CreateMedia,
            ],
            self::Moderator => [
                Permission::AccessAdminPanel,
                Permission::ViewComments, Permission::ModerateComments, Permission::DeleteComments,
                Permission::ViewArticles, Permission::ViewUsers,
            ],
        };
    }

    public function hasPermission(Permission $permission): bool
    {
        return in_array($permission, $this->permissions(), strict: true);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role): array => [$role->value => $role->getLabel()])
            ->all();
    }
}
