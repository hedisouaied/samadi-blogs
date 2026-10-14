<?php

namespace App\Filament\Widgets;

use App\Enums\ArticleStatus;
use App\Enums\CommentStatus;
use App\Enums\Permission;
use App\Enums\SubscriberStatus;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Comments\CommentResource;
use App\Filament\Resources\Media\MediaResource;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Media;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BlogStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Filament::auth()->check();
    }

    protected function getStats(): array
    {
        $user = Filament::auth()->user();

        $stats = [];

        if ($user?->hasPermission(Permission::ViewArticles)) {
            $total = Article::query()->count();
            $published = Article::query()->where('status', ArticleStatus::Published->value)->count();
            $pending = Article::query()->where('status', ArticleStatus::PendingReview->value)->count();

            $stats[] = Stat::make('Articles', (string) $total)
                ->description($published.' published · '.$pending.' in review')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary')
                ->icon('heroicon-o-document-text')
                ->url(ArticleResource::canViewAny() ? ArticleResource::getUrl('index') : null);
        }

        if ($user?->hasPermission(Permission::ViewComments)) {
            $pendingComments = Comment::query()->where('status', CommentStatus::Pending->value)->count();
            $approvedComments = Comment::query()->where('status', CommentStatus::Approved->value)->count();

            $stats[] = Stat::make('Comments', (string) $pendingComments)
                ->description($approvedComments.' approved · '.$pendingComments.' awaiting')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($pendingComments > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->url(CommentResource::canViewAny() ? CommentResource::getUrl('index') : null);
        }

        if ($user?->hasPermission(Permission::ViewSubscribers)) {
            $subscribed = NewsletterSubscriber::query()->where('status', SubscriberStatus::Subscribed->value)->count();
            $totalSubs = NewsletterSubscriber::query()->count();

            $stats[] = Stat::make('Subscribers', (string) $subscribed)
                ->description($totalSubs.' total records')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('success')
                ->icon('heroicon-o-envelope')
                ->url(NewsletterSubscriberResource::canViewAny() ? NewsletterSubscriberResource::getUrl('index') : null);
        }

        if ($user?->hasPermission(Permission::ViewMedia)) {
            $stats[] = Stat::make('Media assets', (string) Media::query()->count())
                ->description('Reusable library files')
                ->descriptionIcon('heroicon-m-photo')
                ->color('info')
                ->icon('heroicon-o-photo')
                ->url(MediaResource::canViewAny() ? MediaResource::getUrl('index') : null);
        }

        if ($user?->hasPermission(Permission::ViewUsers)) {
            $activeUsers = User::query()->active()->count();
            $totalUsers = User::query()->count();

            $stats[] = Stat::make('Team members', (string) $totalUsers)
                ->description($activeUsers.' active')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray')
                ->icon('heroicon-o-users')
                ->url(UserResource::canViewAny() ? UserResource::getUrl('index') : null);
        }

        return $stats;
    }
}
