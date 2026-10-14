@props(['comment', 'article', 'depth' => 0])

@php
    $name = $comment->display_author_name;
    $avatar = $comment->user?->getFilamentAvatarUrl();
    $initial = mb_strtoupper(mb_substr($name, 0, 1));
    $panelId = 'reply-'.$comment->getKey();
@endphp

<li class="{{ $depth > 0 ? 'border-l border-line pl-5 sm:pl-8' : '' }}">
    <article class="flex gap-4">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-sunken text-sm font-semibold text-ink">
            @if ($avatar)
                <img src="{{ $avatar }}" alt="{{ $name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
            @else
                {{ $initial }}
            @endif
        </span>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                <span class="font-medium text-ink">{{ $name }}</span>
                @if ($comment->is_pinned)
                    <span class="inline-flex items-center gap-1 rounded-full bg-accent/10 px-2 py-0.5 text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-accent">
                        Pinned
                    </span>
                @endif
                <time datetime="{{ $comment->created_at?->toIso8601String() }}" class="text-xs text-muted">
                    {{ $comment->created_at?->diffForHumans() }}
                </time>
            </div>

            <div class="mt-3 text-sm leading-relaxed text-ink-soft">
                {!! nl2br(e($comment->content)) !!}
            </div>

            <div class="mt-3 flex items-center gap-4">
                <button type="button" data-reply-toggle data-reply-target="{{ $panelId }}"
                    class="inline-flex items-center gap-1.5 text-xs font-medium uppercase tracking-[0.12em] text-muted transition-colors hover:text-accent">
                    <x-frontend.icon name="chat" class="h-3.5 w-3.5" />
                    Reply
                </button>
            </div>
        </div>
    </article>

    <div id="{{ $panelId }}" data-reply-panel class="mt-4 ml-14 hidden">
        <x-frontend.comment-form :article="$article" :parent-id="$comment->getKey()" compact />
    </div>

    @if ($comment->replies->isNotEmpty())
        <ol class="mt-6 space-y-6">
            @foreach ($comment->replies as $reply)
                <x-frontend.comment-item :comment="$reply" :article="$article" :depth="$depth + 1" />
            @endforeach
        </ol>
    @endif
</li>
