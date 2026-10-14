@props([
    'article',
    'showAuthor' => true,
    'class' => '',
    'dot' => true,
    'variant' => 'default',
])

@php
    $sep = $sep ?? '<span aria-hidden="true" class="text-line-strong">/</span>';
    $mutedClass = $variant === 'on-image' ? 'text-white/75' : 'text-muted';
@endphp

<div {{ $attributes->merge(['class' => "flex flex-wrap items-center gap-x-2.5 gap-y-1 text-[0.75rem] tracking-wide $mutedClass"]) }}>
    @if ($article->categories->first())
        <a href="{{ route('categories.show', $article->categories->first()->slug) }}"
            class="font-semibold uppercase tracking-[0.14em] {{ $variant === 'on-image' ? 'text-white' : 'text-accent' }} transition-opacity hover:opacity-70">
            {{ $article->categories->first()->name }}
        </a>
    @endif

    @if ($article->author)
        <span aria-hidden="true">&middot;</span>
        <span>{{ $article->author->name }}</span>
    @endif

    <span aria-hidden="true">&middot;</span>
    <time datetime="{{ $article->published_at?->toDateString() }}">{{ format_date($article->published_at) }}</time>

    @if ($article->reading_time_minutes)
        <span aria-hidden="true">&middot;</span>
        <span>{{ $article->reading_time_minutes }} min read</span>
    @endif
</div>
