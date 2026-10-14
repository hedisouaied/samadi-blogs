<?php

namespace App\Filament\Resources\ArticleRevisions;

use App\Filament\Resources\ArticleRevisions\Pages\ListArticleRevisions;
use App\Filament\Resources\ArticleRevisions\Pages\ViewArticleRevision;
use App\Filament\Resources\ArticleRevisions\Schemas\ArticleRevisionInfolist;
use App\Filament\Resources\ArticleRevisions\Tables\ArticleRevisionsTable;
use App\Models\ArticleRevision;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ArticleRevisionResource extends Resource
{
    protected static ?string $model = ArticleRevision::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 7;

    protected static ?string $modelLabel = 'revision';

    protected static ?string $navigationLabel = 'Revisions';

    public static function infolist(Schema $schema): Schema
    {
        return ArticleRevisionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArticleRevisionsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticleRevisions::route('/'),
            'view' => ViewArticleRevision::route('/{record}'),
        ];
    }
}
