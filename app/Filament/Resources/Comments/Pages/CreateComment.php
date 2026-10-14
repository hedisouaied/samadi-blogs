<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Enums\CommentStatus;
use App\Filament\Resources\Comments\CommentResource;
use App\Models\Comment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateComment extends CreateRecord
{
    protected static string $resource = CommentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->normalizeModerationState($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeModerationState(array $data): array
    {
        // A reply always belongs to the same article as its parent.
        $parent = null;

        if (! empty($data['parent_id'])) {
            $parent = Comment::find($data['parent_id']);

            if ($parent) {
                $data['article_id'] = $parent->article_id;
            }
        }

        $status = CommentStatus::from($data['status'] ?? CommentStatus::Pending->value);

        // A reply can never be approved before its parent is approved.
        if ($parent && $status === CommentStatus::Approved && $parent->status !== CommentStatus::Approved) {
            $status = $parent->status;
            $data['status'] = $status->value;
        }

        if ($status === CommentStatus::Approved) {
            $data['approved_at'] = now();
            $data['approved_by'] = Auth::id();
        } else {
            $data['approved_at'] = null;
            $data['approved_by'] = null;
        }

        return $data;
    }
}
