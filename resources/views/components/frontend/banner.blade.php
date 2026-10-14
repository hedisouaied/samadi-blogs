@props(['banner', 'variant' => 'wide'])

@if ($banner)
    <a href="{{ $banner->target_url ?: '#' }}"
        @if ($banner->open_in_new_tab) target="_blank" rel="noopener" @endif
        data-reveal
        class="group relative block overflow-hidden rounded-2xl {{ $variant === 'wide' ? 'aspect-[16/9] sm:aspect-[16/6]' : 'aspect-[16/10]' }}">

        @if ($banner->image_url)
            <div class="media media-zoom h-full w-full">
                <img src="{{ $banner->image_url }}" alt="{{ $banner->alt_text ?: $banner->title }}" loading="lazy" decoding="async">
            </div>
        @else
            <div class="h-full w-full bg-gradient-to-br from-ink to-accent"></div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/45 to-transparent"></div>

        <div class="absolute inset-0 flex items-center">
            <div class="max-w-xl p-6 md:p-10">
                @if ($banner->subtitle)
                    <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.2em] text-white/75">{{ $banner->subtitle }}</p>
                @endif
                <h3 class="mt-3 font-display text-2xl text-white md:text-4xl">{{ $banner->title }}</h3>
                @if ($banner->description)
                    <p class="mt-3 hidden max-w-md text-sm leading-relaxed text-white/80 sm:block">{{ $banner->description }}</p>
                @endif
                @if ($banner->button_text)
                    <span class="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-medium text-ink transition-colors group-hover:bg-accent group-hover:text-accent-ink">
                        {{ $banner->button_text }}
                        <x-frontend.icon name="arrow-right" class="h-4 w-4" />
                    </span>
                @endif
            </div>
        </div>

        <span class="absolute right-4 top-4 rounded-full bg-black/40 px-2.5 py-1 text-[0.625rem] uppercase tracking-[0.16em] text-white/70 backdrop-blur">
            Sponsored
        </span>
    </a>
@endif
