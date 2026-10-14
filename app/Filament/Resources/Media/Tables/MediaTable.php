<?php

namespace App\Filament\Resources\Media\Tables;

use App\Models\Media;
use App\Services\MediaService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('url')
                    ->label('Preview')
                    ->height(40)
                    ->width(56)
                    ->grow(false)
                    ->getStateUsing(fn (Media $record): ?string => $record->isImage() ? $record->url : null),
                TextColumn::make('display_name')
                    ->label('Name')
                    ->searchable(['display_name', 'original_filename'])
                    ->weight('semibold')
                    ->description(fn (Media $record): string => $record->original_filename ?: basename((string) $record->path)),
                TextColumn::make('collection')->badge()->color('gray')->placeholder('—')->toggleable(),
                TextColumn::make('mime_type')->label('Type')->badge()->color('info')->toggleable(),
                TextColumn::make('human_size')->label('Size')->toggleable(),
                TextColumn::make('dimensions')->label('Dimensions')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('uploader.name')->label('Uploaded by')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Uploaded')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('collection')
                    ->options(fn (): array => Media::query()
                        ->whereNotNull('collection')
                        ->distinct()
                        ->orderBy('collection')
                        ->pluck('collection', 'collection')
                        ->all()),
                SelectFilter::make('mime_type')
                    ->label('Type')
                    ->options(fn (): array => Media::query()
                        ->whereNotNull('mime_type')
                        ->distinct()
                        ->orderBy('mime_type')
                        ->pluck('mime_type', 'mime_type')
                        ->all()),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('open')
                        ->label('Open file')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (Media $record): ?string => $record->url)
                        ->openUrlInNewTab()
                        ->visible(fn (Media $record): bool => filled($record->url)),
                    Action::make('copyUrl')
                        ->label('Copy URL')
                        ->icon('heroicon-o-clipboard')
                        ->visible(fn (Media $record): bool => filled($record->url))
                        ->action(function (Media $record): void {
                            Notification::make()
                                ->title('URL ready to copy')
                                ->body($record->url)
                                ->success()
                                ->send();
                        }),
                    DeleteAction::make()
                        ->action(function (Media $record): void {
                            app(MediaService::class)->deleteFile($record);
                            $record->delete();
                            Notification::make()->title('Media deleted')->success()->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('deleteWithFiles')
                        ->label('Delete selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('The selected records and their underlying files will be permanently removed.')
                        ->action(function (Collection $records): void {
                            $service = app(MediaService::class);
                            $records->each(function (Media $media) use ($service): void {
                                $service->deleteFile($media);
                                $media->delete();
                            });
                            Notification::make()->title('Media deleted')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading('Media library is empty')
            ->emptyStateDescription('Upload images and documents to reuse across your content.')
            ->emptyStateIcon('heroicon-o-photo');
    }
}
