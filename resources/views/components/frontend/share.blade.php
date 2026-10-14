@props(['article'])

@php
    $url = route('blog.show', $article->slug);
    $title = rawurlencode($article->title);
    $u = rawurlencode($url);
@endphp

<div data-share class="flex flex-wrap items-center gap-2">
    <span class="eyebrow mr-1 hidden sm:inline">Share</span>

    <button type="button" data-share-copy data-share-url="{{ $url }}" aria-label="Copy link"
        class="icon-btn border border-line hover:border-ink">
        <x-frontend.icon name="link" class="h-4 w-4" />
    </button>

    <a href="https://twitter.com/intent/tweet?url={{ $u }}&text={{ $title }}" target="_blank" rel="noopener noreferrer"
        aria-label="Share on X" class="icon-btn border border-line hover:border-ink">
        <x-frontend.icon name="x" class="h-4 w-4" />
    </a>

    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $u }}" target="_blank" rel="noopener noreferrer"
        aria-label="Share on Facebook" class="icon-btn border border-line hover:border-ink">
        <x-frontend.icon name="facebook" class="h-4 w-4" />
    </a>

    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $u }}" target="_blank" rel="noopener noreferrer"
        aria-label="Share on LinkedIn" class="icon-btn border border-line hover:border-ink">
        <x-frontend.icon name="linkedin" class="h-4 w-4" />
    </a>

    <a href="mailto:?subject={{ $title }}&body={{ $u }}" aria-label="Share by email"
        class="icon-btn border border-line hover:border-ink">
        <x-frontend.icon name="envelope" class="h-4 w-4" />
    </a>

    <span data-share-feedback role="status" aria-live="polite" class="ml-1 hidden text-xs text-accent"></span>
</div>
