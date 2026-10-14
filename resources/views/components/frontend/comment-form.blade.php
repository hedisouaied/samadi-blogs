@props(['article', 'parentId' => null, 'compact' => false])

<form data-comment-form data-article="{{ $article->slug }}" action="{{ route('comments.store', $article->slug) }}" method="post"
    class="space-y-4" novalidate>
    @csrf
    @if ($parentId)
        <input type="hidden" name="parent_id" value="{{ $parentId }}">
    @endif

    <div class="absolute h-0 w-0 overflow-hidden" aria-hidden="true">
        <label for="comment-website-{{ $parentId ?? 'root' }}">Website</label>
        <input id="comment-website-{{ $parentId ?? 'root' }}" type="text" name="website" tabindex="-1" autocomplete="off">
    </div>

    @unless ($compact)
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="field-label" for="author_name-{{ $parentId ?? 'root' }}">Name</label>
                <input id="author_name-{{ $parentId ?? 'root' }}" name="author_name" type="text" required
                    value="{{ old('author_name') }}" class="field" placeholder="Your name">
            </div>
            <div>
                <label class="field-label" for="author_email-{{ $parentId ?? 'root' }}">Email</label>
                <input id="author_email-{{ $parentId ?? 'root' }}" name="author_email" type="email" required
                    value="{{ old('author_email') }}" class="field" placeholder="you@example.com">
            </div>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="field-label" for="author_name-{{ $parentId }}">Name</label>
                <input id="author_name-{{ $parentId }}" name="author_name" type="text" required
                    value="{{ old('author_name') }}" class="field !py-2.5" placeholder="Your name">
            </div>
            <div>
                <label class="field-label" for="author_email-{{ $parentId }}">Email</label>
                <input id="author_email-{{ $parentId }}" name="author_email" type="email" required
                    value="{{ old('author_email') }}" class="field !py-2.5" placeholder="you@example.com">
            </div>
        </div>
    @endunless

    <div>
        <label class="field-label" for="content-{{ $parentId ?? 'root' }}">Comment</label>
        <textarea id="content-{{ $parentId ?? 'root' }}" name="content" rows="{{ $compact ? 3 : 5 }}" required
            class="field resize-y" placeholder="Share your thoughts…">{{ old('content') }}</textarea>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-xs text-muted">
            Your email is never published. Comments are moderated.
        </p>
        <button type="submit" class="btn btn-primary">
            <span data-comment-label>{{ $parentId ? 'Post reply' : 'Post comment' }}</span>
            <x-frontend.icon name="arrow-right" class="h-4 w-4" />
        </button>
    </div>

    <p data-comment-message role="status" aria-live="polite" class="hidden text-sm text-accent"></p>
</form>
