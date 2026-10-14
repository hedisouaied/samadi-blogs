<?php

namespace App\Filament\Widgets;

use App\Enums\ArticleStatus;
use App\Enums\Permission;
use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestArticles extends TableWidget
{
    protected static ?string $heading = 'Latest articles';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return Filament::auth()->user()?->hasPermission(Permission::ViewArticles) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Article::query()->latest('created_at')->limit(5))
            ->columns([
                TextColumn::make('title')->limit(40)->wrap()->weight('semibold'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ArticleStatus $state): string => $state->getLabel())
                    ->color(fn (ArticleStatus $state): string => $state->getColor()),
                TextColumn::make('author.name')->label('Author')->toggleable(),
                TextColumn::make('created_at')->label('Created')->since(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Article $record): string => ArticleResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false);
    }
}
