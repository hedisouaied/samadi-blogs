@props([
    'article',
    'variant' => 'standard',
    'delay' => 0,
    'priority' => false,
    'index' => null,
])

@php
    $url = route('blog.show', $article->slug);
    $category = $article->categories->first();
    $image = $article->featured_image_url;
    $alt = $article->featured_image_alt ?: $article->title;
    $loading = $priority ? 'eager' : 'lazy';
    $excerpt = $article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $article->content), 150);
@endphp

@switch($variant)

    @case('hero')
        <a href="{{ $url }}" data-reveal style="--reveal-delay: {{ $delay }}ms"
            class="group relative block overflow-hidden rounded-2xl">
            <div class="media media-zoom aspect-[4/5] sm:aspect-[16/10] md:aspect-[16/8]">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $alt }}" loading="{{ $loading }}" decoding="async"
                        @if ($priority) fetchpriority="high" @endif>
                @else
                    <div class="flex h-full w-full items-center justify-center bg-ink/20">
                        <span class="font-display text-6xl text-white/40">{{ $category?->name }}</span>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/5"></div>
            </div>
            <div class="absolute inset-x-0 bottom-0 p-6 md:p-10 lg:p-12">
                @if ($category)
                    <span class="inline-flex items-center rounded-full bg-accent px-3 py-1 text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-accent-ink">
                        {{ $category->name }}
                    </span>
                @endif
                <h2 class="mt-4 max-w-3xl font-display text-fluid-display text-white">
                    {{ $article->title }}
                </h2>
                <div class="mt-5 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-[0.75rem] tracking-wide text-white/80">
                    @if ($article->author)<span>{{ $article->author->name }}</span><span aria-hidden="true">&middot;</span>@endif
                    <time datetime="{{ $article->published_at?->toDateString() }}">{{ format_date($article->published_at) }}</time>
                    @if ($article->reading_time_minutes)<span aria-hidden="true">&middot;</span><span>{{ $article->reading_time_minutes }} min read</span>@endif
                </div>
            </div>
        </a>
        @break

    @case('overlay')
        <a href="{{ $url }}" data-reveal style="--reveal-delay: {{ $delay }}ms"
            class="group relative block overflow-hidden rounded-xl">
            <div class="media media-zoom aspect-[4/3]">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $alt }}" loading="{{ $loading }}" decoding="async">
                @else
                    <div class="h-full w-full bg-sunken"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
            </div>
            <div class="absolute inset-x-0 bottom-0 p-5">
                <h3 class="font-display text-xl leading-tight text-white">{{ $article->title }}</h3>
                <p class="mt-2 text-[0.6875rem] uppercase tracking-[0.14em] text-white/70">{{ format_date($article->published_at) }}</p>
            </div>
        </a>
        @break

    @case('feature')
        <a href="{{ $url }}" data-reveal style="--reveal-delay: {{ $delay }}ms" class="group card block">
            <div class="media media-zoom aspect-[4/3] overflow-hidden rounded-xl">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $alt }}" loading="{{ $loading }}" decoding="async"
                        @if ($priority) fetchpriority="high" @endif>
                @else
                    <div class="flex h-full w-full items-center justify-center bg-sunken">
                        <span class="font-display text-4xl text-line-strong">{{ $category?->name }}</span>
                    </div>
                @endif
            </div>
            <div class="mt-5">
                @if ($category)
                    <span class="tap-cat">{{ $category->name }}</span>
                @endif
                <h3 class="card-title mt-3 text-[1.625rem] leading-[1.08] md:text-3xl">{{ $article->title }}</h3>
                <p class="mt-3 line-clamp-2 text-sm leading-relaxed text-muted">{{ $excerpt }}</p>
                <div class="mt-4 flex flex-wrap items-center gap-x-2.5 text-[0.75rem] text-muted">
                    <time datetime="{{ $article->published_at?->toDateString() }}">{{ format_date($article->published_at) }}</time>
                    @if ($article->reading_time_minutes)<span aria-hidden="true">&middot;</span><span>{{ $article->reading_time_minutes }} min read</span>@endif
                </div>
            </div>
        </a>
        @break

    @case('list')
        <a href="{{ $url }}" data-reveal style="--reveal-delay: {{ $delay }}ms" class="group card flex items-start gap-5">
            <div class="media media-zoom aspect-[4/3] w-28 shrink-0 overflow-hidden rounded-lg sm:w-44">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $alt }}" loading="{{ $loading }}" decoding="async">
                @else
                    <div class="h-full w-full bg-sunken"></div>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                @if ($category)<span class="tap-cat">{{ $category->name }}</span>@endif
                <h3 class="card-title mt-2 text-lg leading-snug sm:text-xl">{{ $article->title }}</h3>
                <p class="mt-2 hidden line-clamp-2 text-sm leading-relaxed text-muted sm:block">{{ $excerpt }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-x-2.5 text-[0.75rem] text-muted">
                    <time datetime="{{ $article->published_at?->toDateString() }}">{{ format_date($article->published_at) }}</time>
                    @if ($article->reading_time_minutes)<span aria-hidden="true">&middot;</span><span>{{ $article->reading_time_minutes }} min</span>@endif
                </div>
            </div>
        </a>
        @break

    @case('compact')
        <a href="{{ $url }}" data-reveal style="--reveal-delay: {{ $delay }}ms" class="group card flex items-start gap-4">
            <div class="media media-zoom aspect-square w-20 shrink-0 overflow-hidden rounded-lg">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $alt }}" loading="{{ $loading }}" decoding="async">
                @else
                    <div class="h-full w-full bg-sunken"></div>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="card-title text-base leading-snug">{{ $article->title }}</h3>
                <p class="mt-1.5 text-[0.6875rem] uppercase tracking-[0.12em] text-muted">{{ format_date($article->published_at) }}</p>
            </div>
        </a>
        @break

    @case('minimal')
        <a href="{{ $url }}" data-reveal style="--reveal-delay: {{ $delay }}ms" class="group flex items-start gap-4">
            @if ($index)
                <span class="font-display text-2xl leading-none text-line-strong transition-colors group-hover:text-accent">
                    {{ str_pad((string) $index, 2, '0', STR_PAD_LEFT) }}
                </span>
            @endif
            <div class="min-w-0">
                <h3 class="card-title text-lg leading-snug">{{ $article->title }}</h3>
                <p class="mt-1.5 text-[0.6875rem] uppercase tracking-[0.12em] text-muted">
                    {{ format_date($article->published_at) }}
                    @if ($article->reading_time_minutes) &middot; {{ $article->reading_time_minutes }} min @endif
                </p>
            </div>
        </a>
        @break

    @default
        <a href="{{ $url }}" data-reveal style="--reveal-delay: {{ $delay }}ms" class="group card block">
            <div class="media media-zoom aspect-[3/2] overflow-hidden rounded-xl">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $alt }}" loading="{{ $loading }}" decoding="async">
                @else
                    <div class="flex h-full w-full items-center justify-center bg-sunken">
                        <span class="font-display text-3xl text-line-strong">{{ $category?->name }}</span>
                    </div>
                @endif
            </div>
            <div class="mt-4">
                @if ($category)<span class="tap-cat">{{ $category->name }}</span>@endif
                <h3 class="card-title mt-2.5 text-xl leading-snug">{{ $article->title }}</h3>
                <div class="mt-3 flex flex-wrap items-center gap-x-2.5 text-[0.75rem] text-muted">
                    <time datetime="{{ $article->published_at?->toDateString() }}">{{ format_date($article->published_at) }}</time>
                    @if ($article->reading_time_minutes)<span aria-hidden="true">&middot;</span><span>{{ $article->reading_time_minutes }} min read</span>@endif
                </div>
            </div>
        </a>
@endswitch
