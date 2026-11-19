<?php

namespace App\Providers;

use App\Models\User;
use App\Services\SettingsService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Field;
use Filament\Infolists\Components\Entry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
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

        $this->translateFilamentLabels();
    }

    protected function translateFilamentLabels(): void
    {
        Field::configureUsing(fn (Field $component) => $component->translateLabel());
        Entry::configureUsing(fn (Entry $component) => $component->translateLabel());
        Column::configureUsing(fn (Column $component) => $component->translateLabel());
        BaseFilter::configureUsing(fn (BaseFilter $component) => $component->translateLabel());
        Tab::configureUsing(fn (Tab $component) => $component->translateLabel());
        Fieldset::configureUsing(fn (Fieldset $component) => $component->translateLabel());
        Action::configureUsing(fn (Action $component) => $component->translateLabel());
        ActionGroup::configureUsing(fn (ActionGroup $component) => $component->translateLabel());
    }
}
