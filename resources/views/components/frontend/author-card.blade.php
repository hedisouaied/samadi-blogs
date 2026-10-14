@props(['author', 'delay' => 0, 'variant' => 'card'])

@php
    $avatar = $author->getFilamentAvatarUrl();
    $url = route('authors.show', $author->username ?: $author->getKey());
    $count = $author->published_articles_count ?? null;
@endphp

@if ($variant === 'inline')
    <a href="{{ $url }}" class="group inline-flex items-center gap-3">
        <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-sunken text-xs font-semibold text-ink">
            @if ($avatar)
                <img src="{{ $avatar }}" alt="{{ $author->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
            @else
                {{ $author->initials }}
            @endif
        </span>
        <span class="text-sm font-medium text-ink transition-colors group-hover:text-accent">{{ $author->name }}</span>
    </a>
@else
    <a href="{{ $url }}" data-reveal style="--reveal-delay: {{ $delay }}ms" class="group block text-center">
        <span class="mx-auto flex h-24 w-24 items-center justify-center overflow-hidden rounded-full bg-sunken font-display text-3xl text-ink ring-1 ring-line transition-all duration-500 group-hover:ring-accent">
            @if ($avatar)
                <img src="{{ $avatar }}" alt="{{ $author->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
            @else
                {{ $author->initials }}
            @endif
        </span>
        <h3 class="mt-5 font-display text-xl text-ink transition-colors group-hover:text-accent">{{ $author->name }}</h3>
        @if ($author->bio)
            <p class="mx-auto mt-2 max-w-xs line-clamp-2 text-sm leading-relaxed text-muted">{{ $author->bio }}</p>
        @endif
        @if ($count)
            <p class="mt-3 text-[0.6875rem] uppercase tracking-[0.14em] text-muted">{{ $count }} {{ \Illuminate\Support\Str::plural('essay', $count) }}</p>
        @endif
    </a>
@endif
