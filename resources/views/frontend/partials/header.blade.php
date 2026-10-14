@inject('contentService', 'App\Services\PublicContentService')
@php
    $navItems = $contentService->navigation('primary');
    $tagline = setting('general.site_tagline', '');
@endphp

<header data-site-header data-scrolled="false"
    class="sticky top-0 z-50 border-b border-transparent bg-bg/70 transition-colors duration-300 data-[scrolled=true]:border-line data-[scrolled=true]:bg-bg/85 data-[scrolled=true]:backdrop-blur-xl">

    <div class="hidden border-b border-line/60 md:block">
        <div class="shell flex h-9 items-center justify-between text-[0.6875rem] uppercase tracking-[0.18em] text-muted">
            <span>{{ format_date(now(), 'l, F j, Y') }}</span>
            <div class="flex items-center gap-5">
                <a href="{{ route('feed') }}" class="link-underline inline-flex items-center gap-1.5 hover:text-ink">
                    <x-frontend.icon name="rss" class="h-3.5 w-3.5" /> RSS
                </a>
                <a href="#newsletter" class="link-underline hover:text-ink">Newsletter</a>
            </div>
        </div>
    </div>

    <div class="shell">
        <div class="grid h-16 grid-cols-[1fr_auto_1fr] items-center gap-4 md:h-20">

            {{-- Left: navigation --}}
            <div class="flex items-center gap-4">
                <button type="button" data-mobile-toggle aria-label="Open menu" aria-expanded="false"
                    aria-controls="mobile-nav"
                    class="icon-btn -ml-2 lg:hidden">
                    <x-frontend.icon name="menu" />
                </button>

                <nav aria-label="Primary" class="hidden lg:block">
                    <ul class="flex items-center gap-8">
                        @foreach ($navItems as $item)
                            <li class="group relative">
                                @if (! empty($item['children']))
                                    <button type="button"
                                        class="inline-flex items-center gap-1 text-[0.8125rem] font-medium tracking-wide text-ink-soft transition-colors hover:text-ink">
                                        {{ $item['label'] }}
                                        <x-frontend.icon name="chevron-down" class="h-3.5 w-3.5 transition-transform duration-300 group-hover:rotate-180" />
                                    </button>
                                    <div class="invisible absolute left-0 top-full z-20 w-56 translate-y-2 pt-3 opacity-0 transition-all duration-300 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100">
                                        <ul class="overflow-hidden rounded-xl border border-line bg-surface p-2 shadow-xl shadow-black/5">
                                            @foreach ($item['children'] as $child)
                                                <li>
                                                    <a href="{{ $child['url'] }}"
                                                        class="block rounded-lg px-3 py-2 text-sm text-ink-soft transition-colors hover:bg-sunken hover:text-ink">
                                                        {{ $child['label'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <a href="{{ $item['url'] }}"
                                        @if ($item['new_tab']) target="_blank" rel="noopener" @endif
                                        class="link-underline text-[0.8125rem] font-medium tracking-wide text-ink-soft transition-colors hover:text-ink">
                                        {{ $item['label'] }}
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </div>

            {{-- Center: wordmark --}}
            <a href="{{ route('home') }}" class="group flex flex-col items-center justify-center text-center"
                aria-label="{{ $siteName }} — home">
                <span class="font-display text-2xl leading-none tracking-tight text-ink transition-colors group-hover:text-accent md:text-[1.75rem]">
                    {{ $siteName }}
                </span>
                @if ($tagline)
                    <span class="mt-1 hidden text-[0.625rem] uppercase tracking-[0.3em] text-muted md:block">{{ $tagline }}</span>
                @endif
            </a>

            {{-- Right: actions --}}
            <div class="flex items-center justify-end gap-1">
                <button type="button" data-search-open aria-label="Search" class="icon-btn">
                    <x-frontend.icon name="search" />
                </button>

                <button type="button" data-theme-toggle aria-label="Toggle color theme" class="icon-btn">
                    <x-frontend.icon name="sun" class="h-5 w-5 dark:hidden" />
                    <x-frontend.icon name="moon" class="hidden h-5 w-5 dark:block" />
                </button>

                <a href="#newsletter"
                    class="btn btn-primary ml-2 hidden !px-5 !py-2.5 !text-[0.8125rem] xl:inline-flex">
                    Subscribe
                </a>
            </div>
        </div>
    </div>
</header>
