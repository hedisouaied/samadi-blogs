<?php

namespace App\Providers;

use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Auth\Events\Login;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsService::class, fn (): SettingsService => new SettingsService);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
            }
        });

        Paginator::defaultView('vendor.pagination.editorial');
        Paginator::defaultSimpleView('vendor.pagination.editorial-simple');
    }
}
