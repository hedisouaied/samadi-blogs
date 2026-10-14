<?php

namespace App\Enums;

enum Permission: string
{
    case AccessAdminPanel = 'admin.access';

    case ViewUsers = 'users.view';
    case CreateUsers = 'users.create';
    case UpdateUsers = 'users.update';
    case DeleteUsers = 'users.delete';
    case ManageUserRoles = 'users.roles';

    case ViewArticles = 'articles.view';
    case CreateArticles = 'articles.create';
    case UpdateAnyArticle = 'articles.update.any';
    case UpdateOwnArticle = 'articles.update.own';
    case DeleteArticles = 'articles.delete';
    case PublishArticles = 'articles.publish';
    case ReviewArticles = 'articles.review';

    case ViewCategories = 'categories.view';
    case CreateCategories = 'categories.create';
    case UpdateCategories = 'categories.update';
    case DeleteCategories = 'categories.delete';

    case ViewTags = 'tags.view';
    case CreateTags = 'tags.create';
    case UpdateTags = 'tags.update';
    case DeleteTags = 'tags.delete';

    case ViewComments = 'comments.view';
    case ModerateComments = 'comments.moderate';
    case DeleteComments = 'comments.delete';

    case ViewBanners = 'banners.view';
    case CreateBanners = 'banners.create';
    case UpdateBanners = 'banners.update';
    case DeleteBanners = 'banners.delete';

    case ViewSubscribers = 'subscribers.view';
    case CreateSubscribers = 'subscribers.create';
    case UpdateSubscribers = 'subscribers.update';
    case DeleteSubscribers = 'subscribers.delete';
    case ExportSubscribers = 'subscribers.export';

    case ViewMedia = 'media.view';
    case CreateMedia = 'media.create';
    case DeleteMedia = 'media.delete';

    case ViewMenus = 'menus.view';
    case ManageMenus = 'menus.manage';

    case ViewSettings = 'settings.view';
    case ManageSettings = 'settings.manage';

    case ViewActivity = 'activity.view';
    case DeleteActivity = 'activity.delete';
}
