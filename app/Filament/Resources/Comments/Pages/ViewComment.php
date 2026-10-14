<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Enums\CommentStatus;
use App\Enums\Permission;
use App\Filament\Resources\Comments\CommentResource;
use App\Services\CommentService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewComment extends ViewRecord
{
    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        $canModerate = Auth::user()?->hasPermission(Permission::ModerateComments) ?? false;
        $canDelete = Auth::user()?->hasPermission(Permission::DeleteComments) ?? false;

        $actions = [EditAction::make()];

        if ($canModerate) {
            $actions[] = Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->getRecord()->status !== CommentStatus::Approved)
                ->action(function (): void {
                    $ok = app(CommentService::class)->approve($this->getRecord(), Auth::user());

                    if ($ok) {
                        Notification::make()->title('Comment approved')->success()->send();
                    } else {
                        Notification::make()
                            ->title('Cannot approve yet')
                            ->body('Approve the parent comment first.')
                            ->warning()
                            ->send();
                    }
                });

            $actions[] = Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action(function (): void {
                    app(CommentService::class)->reject($this->getRecord(), Auth::user());
                    Notification::make()->title('Comment rejected')->success()->send();
                });
        }

        if ($canDelete) {
            $actions[] = Action::make('delete')
                ->label('Delete')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('Replies are preserved and re-attached to this comment\'s parent.')
                ->action(function (): void {
                    app(CommentService::class)->deletePreservingReplies($this->getRecord(), Auth::user());
                    Notification::make()->title('Comment deleted')->success()->send();

                    $this->redirect(CommentResource::getUrl('index'));
                });
        }

        return $actions;
    }
}
