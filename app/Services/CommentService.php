<?php

namespace App\Services;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Comment moderation and threaded-reply handling.
 *
 * Nesting policy: replies may be nested up to Comment::MAX_DEPTH levels.
 * When a comment is deleted, its direct replies are re-parented to the
 * deleted comment's parent so the thread is preserved rather than destroyed.
 */
class CommentService
{
    public function __construct(
        protected ActivityLogger $activity,
    ) {}

    public function approve(Comment $comment, ?User $actor = null): bool
    {
        if (! $comment->canBeApproved()) {
            return false;
        }

        $comment->status = CommentStatus::Approved;
        $comment->approved_at = now();
        $comment->approved_by = $actor?->getKey();
        $comment->save();

        $this->activity->log('comment.approved', $comment, 'Approved a comment', [], $actor?->getKey());

        return true;
    }

    public function reject(Comment $comment, ?User $actor = null): void
    {
        $comment->status = CommentStatus::Rejected;
        $comment->approved_at = null;
        $comment->approved_by = null;
        $comment->save();

        $this->activity->log('comment.rejected', $comment, 'Rejected a comment', [], $actor?->getKey());
    }

    public function markAsSpam(Comment $comment, ?User $actor = null): void
    {
        $comment->status = CommentStatus::Spam;
        $comment->approved_at = null;
        $comment->approved_by = null;
        $comment->save();

        $this->activity->log('comment.spam', $comment, 'Marked a comment as spam', [], $actor?->getKey());
    }

    public function moveToTrash(Comment $comment, ?User $actor = null): void
    {
        $comment->status = CommentStatus::Trash;
        $comment->save();

        $this->activity->log('comment.trashed', $comment, 'Moved a comment to trash', [], $actor?->getKey());
    }

    public function togglePinned(Comment $comment, ?User $actor = null): void
    {
        $comment->is_pinned = ! $comment->is_pinned;
        $comment->save();

        $this->activity->log(
            $comment->is_pinned ? 'comment.pinned' : 'comment.unpinned',
            $comment,
            ($comment->is_pinned ? 'Pinned' : 'Unpinned').' a comment',
            [],
            $actor?->getKey(),
        );
    }

    /**
     * Delete a comment while preserving its replies by re-parenting them.
     */
    public function deletePreservingReplies(Comment $comment, ?User $actor = null): void
    {
        DB::transaction(function () use ($comment, $actor): void {
            $comment->replies()->update(['parent_id' => $comment->parent_id]);
            $comment->delete();

            $this->activity->log('comment.deleted', null, 'Deleted a comment', [
                'article_id' => $comment->article_id,
            ], $actor?->getKey());
        });
    }

    /**
     * Create a reply, enforcing the nesting policy and article consistency.
     *
     * @param  array{content: string, user_id?: int|null, author_name?: string|null, author_email?: string|null, status?: CommentStatus}  $data
     */
    public function reply(Comment $parent, array $data, ?User $actor = null): Comment
    {
        $effectiveParent = $this->resolveParent($parent);

        return DB::transaction(function () use ($effectiveParent, $data, $actor): Comment {
            $reply = new Comment;
            $reply->article_id = $effectiveParent->article_id;
            $reply->parent_id = $effectiveParent->getKey();
            $reply->user_id = $data['user_id'] ?? $actor?->getKey();
            $reply->author_name = $data['author_name'] ?? $actor?->name;
            $reply->author_email = $data['author_email'] ?? $actor?->email;
            $reply->content = $data['content'];
            $reply->status = $data['status'] ?? CommentStatus::Pending;
            $reply->save();

            $this->activity->log('comment.replied', $reply, 'Replied to a comment', [], $actor?->getKey());

            return $reply;
        });
    }

    /**
     * Submit a new (root-level) public comment.
     *
     * @param  array{author_name?: ?string, author_email?: ?string, content: string, auto_approve?: bool}  $data
     */
    public function submit(\App\Models\Article $article, array $data, ?User $actor = null, ?string $ip = null, ?string $userAgent = null): Comment
    {
        $autoApprove = (bool) ($data['auto_approve'] ?? false);

        $comment = new Comment;
        $comment->article_id = $article->getKey();
        $comment->user_id = $actor?->getKey();
        $comment->parent_id = null;
        $comment->author_name = $data['author_name'] ?? $actor?->name;
        $comment->author_email = $data['author_email'] ?? $actor?->email;
        $comment->content = $data['content'];
        $comment->status = $autoApprove ? CommentStatus::Approved : CommentStatus::Pending;
        $comment->approved_at = $autoApprove ? now() : null;
        $comment->ip_address = $ip;
        $comment->user_agent = $userAgent;
        $comment->save();

        $this->activity->log('comment.submitted', $comment, 'Submitted a comment', [], $actor?->getKey());

        return $comment;
    }

    /**
     * Ensure a reply is never attached deeper than MAX_DEPTH.
     */
    protected function resolveParent(Comment $parent): Comment
    {
        $depth = $parent->depth();
        $current = $parent;

        while ($depth >= Comment::MAX_DEPTH && $current->parent_id !== null) {
            $current = $current->parent;
            $depth--;
        }

        return $current;
    }
}
