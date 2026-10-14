<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\CommentStatus;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\User;
use App\Services\CommentService;
use App\Services\PublicContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class CommentController extends Controller
{
    public function __construct(
        protected PublicContentService $content,
        protected CommentService $comments,
    ) {}

    public function store(Request $request, string $slug)
    {
        $article = $this->content->publishedArticle($slug);
        $moderation = (string) setting('content.comment_moderation', 'manual');

        if (! $article->allow_comments || $moderation === 'disabled') {
            return $this->fail($request, 'Comments are closed on this article.');
        }

        if ($request->filled('website')) {
            return $this->succeed($request, 'Thanks — your comment has been received.');
        }

        $data = $request->validate([
            'author_name' => ['required', 'string', 'min:2', 'max:80'],
            'author_email' => ['required', 'email:rfc', 'max:255'],
            'content' => ['required', 'string', 'min:3', 'max:5000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        $key = 'comment:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 4)) {
            return $this->fail($request, 'You are commenting too quickly. Please wait a moment.', 429);
        }

        RateLimiter::hit($key, 120);

        $actor = $request->user() instanceof User ? $request->user() : null;
        $autoApprove = $moderation === 'automatic';

        $parent = null;

        if (! empty($data['parent_id'])) {
            $parent = Comment::query()
                ->whereKey($data['parent_id'])
                ->where('article_id', $article->getKey())
                ->approved()
                ->first();
        }

        if ($parent) {
            $this->comments->reply($parent, [
                'author_name' => $data['author_name'],
                'author_email' => $data['author_email'],
                'content' => $data['content'],
                'status' => $autoApprove ? CommentStatus::Approved : CommentStatus::Pending,
            ], $actor);
        } else {
            $this->comments->submit($article, [
                'author_name' => $data['author_name'],
                'author_email' => $data['author_email'],
                'content' => $data['content'],
                'auto_approve' => $autoApprove,
            ], $actor, $request->ip(), substr((string) $request->userAgent(), 0, 255));
        }

        $message = $autoApprove
            ? 'Your comment is live.'
            : 'Thanks! Your comment is awaiting moderation.';

        return $this->succeed($request, $message);
    }

    protected function succeed(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return back()->with('comment_status', $message)->withFragment('comments');
    }

    protected function fail(Request $request, string $message, int $status = 422)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => false, 'message' => $message], $status);
        }

        return back()->withErrors(['comment' => $message]);
    }
}
