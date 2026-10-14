@props(['category', 'delay' => 0, 'size' => 'md'])

@php
    $count = $category->published_articles_count ?? null;
    $height = $size === 'lg' ? 'aspect-[16/10] md:aspect-[16/7]' : 'aspect-[4/3]';
@endphp

<a href="{{ route('categories.show', $category->slug) }}" data-reveal style="--reveal-delay: {{ $delay }}ms"
    class="group relative block overflow-hidden rounded-xl">
    <div class="media media-zoom {{ $height }}">
        @if ($category->image_url)
            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" loading="lazy" decoding="async">
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-ink to-accent/70">
                <span class="font-display text-3xl text-white/70">{{ $category->name }}</span>
            </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
    </div>

    <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-4 p-5">
        <div>
            <h3 class="font-display text-xl text-white md:text-2xl">{{ $category->name }}</h3>
            @if ($count)
                <p class="mt-1 text-[0.6875rem] uppercase tracking-[0.14em] text-white/70">{{ $count }} {{ \Illuminate\Support\Str::plural('article', $count) }}</p>
            @endif
        </div>
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/30 text-white transition-all duration-300 group-hover:bg-white group-hover:text-ink">
            <x-frontend.icon name="arrow-up-right" class="h-4 w-4" />
        </span>
    </div>
</a>
