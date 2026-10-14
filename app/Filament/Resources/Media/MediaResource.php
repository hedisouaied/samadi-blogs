<?php

namespace App\Filament\Resources\Media;

use App\Filament\Resources\Media\Pages\CreateMedia;
use App\Filament\Resources\Media\Pages\EditMedia;
use App\Filament\Resources\Media\Pages\ListMedia;
use App\Filament\Resources\Media\Pages\ViewMedia;
use App\Filament\Resources\Media\Schemas\MediaForm;
use App\Filament\Resources\Media\Schemas\MediaInfolist;
use App\Filament\Resources\Media\Tables\MediaTable;
use App\Models\Media;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'display_name';

    protected static ?string $navigationLabel = 'Media library';

    public static function form(Schema $schema): Schema
    {
        return MediaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MediaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MediaTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'gray';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
            'create' => CreateMedia::route('/create'),
            'view' => ViewMedia::route('/{record}'),
            'edit' => EditMedia::route('/{record}/edit'),
        ];
    }
}
