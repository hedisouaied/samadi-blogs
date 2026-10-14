@props(['tag', 'active' => false, 'count' => false])

<a href="{{ route('tags.show', $tag->slug) }}"
    @if ($active) aria-current="page" @endif
    class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm transition-colors
        {{ $active ? 'border-ink bg-ink text-bg' : 'border-line text-ink-soft hover:border-ink hover:text-ink' }}">
    <span>{{ $tag->name }}</span>
    @if ($count && ($tag->published_articles_count ?? null))
        <span class="text-[0.6875rem] {{ $active ? 'text-bg/70' : 'text-muted' }}">{{ $tag->published_articles_count }}</span>
    @endif
</a>
