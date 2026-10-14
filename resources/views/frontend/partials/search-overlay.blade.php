@inject('contentService', 'App\Services\PublicContentService')
@php
    $suggestedTags = $contentService->tags(6);
@endphp

<div data-search-overlay class="fixed inset-0 z-[80] hidden" role="dialog" aria-modal="true" aria-label="Search">
    <div data-search-backdrop class="absolute inset-0 bg-bg/95 opacity-0 backdrop-blur-xl transition-opacity duration-300"></div>

    <div class="relative shell pt-24 md:pt-32">
        <div class="mx-auto max-w-2xl">

            <div class="mb-8 flex items-center justify-between">
                <span class="eyebrow">Search the archive</span>
                <button type="button" data-search-close class="icon-btn" aria-label="Close search">
                    <x-frontend.icon name="close" />
                </button>
            </div>

            <form action="{{ route('search') }}" method="get" role="search">
                <label for="site-search" class="sr-only">Search</label>
                <div class="relative">
                    <x-frontend.icon name="search" class="pointer-events-none absolute left-0 top-1/2 h-6 w-6 -translate-y-1/2 text-muted" />
                    <input id="site-search" data-search-input type="search" name="q" autocomplete="off" enterkeyhint="search"
                        placeholder="Search essays, topics and authors…"
                        class="w-full border-0 border-b-2 border-line bg-transparent py-5 pl-11 pr-4 font-display text-3xl text-ink placeholder:text-muted focus:border-accent focus:outline-none md:text-4xl">
                    <span class="pointer-events-none absolute bottom-5 right-0 hidden text-[0.625rem] uppercase tracking-[0.2em] text-muted md:block">
                        Press Enter
                    </span>
                </div>
            </form>

            @if ($suggestedTags->isNotEmpty())
                <div class="mt-10">
                    <p class="eyebrow mb-4">Popular topics</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($suggestedTags as $tag)
                            <a href="{{ route('tags.show', $tag->slug) }}"
                                class="rounded-full border border-line px-4 py-2 text-sm text-ink-soft transition-colors hover:border-ink hover:text-ink">
                                {{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
