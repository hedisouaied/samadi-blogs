@props([
    'eyebrow' => null,
    'title' => null,
    'description' => null,
    'href' => null,
    'linkLabel' => 'View all',
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-end justify-between gap-6 border-b border-line pb-6']) }}>
    <div class="max-w-2xl">
        @if ($eyebrow)
            <p class="eyebrow eyebrow-accent">{{ $eyebrow }}</p>
        @endif
        @if ($title)
            <h2 class="mt-2 font-display text-fluid-title text-ink">{{ $title }}</h2>
        @endif
        @if ($description)
            <p class="mt-3 text-sm leading-relaxed text-muted">{{ $description }}</p>
        @endif
    </div>

    @if ($href)
        <a href="{{ $href }}"
            class="link-underline group inline-flex items-center gap-2 whitespace-nowrap text-sm font-medium text-ink">
            {{ $linkLabel }}
            <x-frontend.icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
        </a>
    @endif
</div>
