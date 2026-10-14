<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Enums\CommentStatus;
use App\Filament\Resources\Comments\CommentResource;
use App\Models\Comment;
use App\Services\CommentService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditComment extends EditRecord
{
    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalDescription('Replies are preserved and re-attached to this comment\'s parent.')
                ->action(function (Comment $record): void {
                    app(CommentService::class)->deletePreservingReplies($record, Auth::user());

                    Notification::make()
                        ->title('Comment deleted')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        if (! empty($data['parent_id'])) {
            $parent = Comment::find($data['parent_id']);

            if ($parent && $parent->getKey() !== $record->getKey()) {
                $data['article_id'] = $parent->article_id;
            }
        }

        $status = CommentStatus::from($data['status'] ?? CommentStatus::Pending->value);
        $parent = $record->parent;

        if ($parent && $status === CommentStatus::Approved && $parent->status !== CommentStatus::Approved) {
            $status = $parent->status;
            $data['status'] = $status->value;
        }

        if ($status === CommentStatus::Approved) {
            $data['approved_at'] = $record->approved_at ?? now();
            $data['approved_by'] = $record->approved_by ?? Auth::id();
        } else {
            $data['approved_at'] = null;
            $data['approved_by'] = null;
        }

        return $data;
    }
}
