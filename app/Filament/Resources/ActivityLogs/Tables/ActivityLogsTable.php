<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Enums\Permission;
use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Models\ActivityLog;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('M j, Y H:i:s')
                    ->sortable(),
                TextColumn::make('action_label')
                    ->label('Action')
                    ->badge()
                    ->color('info'),
                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('System')
                    ->searchable(['users.name']),
                TextColumn::make('description')
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('subject_type')
                    ->label('Subject type')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->options(fn (): array => ActivityLog::query()
                        ->distinct()
                        ->orderBy('action')
                        ->pluck('action', 'action')
                        ->all()),
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('subject_type')
                    ->label('Subject type')
                    ->options(fn (): array => ActivityLog::query()
                        ->whereNotNull('subject_type')
                        ->distinct()
                        ->orderBy('subject_type')
                        ->pluck('subject_type')
                        ->mapWithKeys(fn ($type): array => [$type => class_basename($type)])
                        ->all()),
                Filter::make('created_at')
                    ->label('Date range')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record): string => ActivityLogResource::getUrl('view', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::DeleteActivity) ?? false),
                ]),
            ])
            ->emptyStateHeading('No activity yet')
            ->emptyStateDescription('Administrative events are recorded here.')
            ->emptyStateIcon('heroicon-o-chart-bar-square');
    }
}
