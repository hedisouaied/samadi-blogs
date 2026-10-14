@inject('contentService', 'App\Services\PublicContentService')
@php
    $navItems = $contentService->navigation('primary');
@endphp

<div id="mobile-nav" data-mobile-nav class="fixed inset-0 z-[70] hidden" aria-hidden="true">
    <div data-mobile-backdrop class="absolute inset-0 bg-ink/40 opacity-0 backdrop-blur-sm transition-opacity duration-500"></div>

    <div data-mobile-panel
        class="absolute right-0 top-0 flex h-full w-[88%] max-w-sm translate-x-full flex-col border-l border-line bg-surface transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]">

        <div class="flex items-center justify-between border-b border-line px-6 py-5">
            <span class="font-display text-xl text-ink">{{ $siteName ?? setting('general.site_name') }}</span>
            <button type="button" data-mobile-close aria-label="Close menu" class="icon-btn">
                <x-frontend.icon name="close" />
            </button>
        </div>

        <nav aria-label="Mobile" class="flex-1 overflow-y-auto px-6 py-6">
            <ul class="space-y-1">
                @foreach ($navItems as $item)
                    <li>
                        @if (! empty($item['children']))
                            <details class="group">
                                <summary
                                    class="flex cursor-pointer list-none items-center justify-between py-3 font-display text-2xl text-ink marker:content-none">
                                    {{ $item['label'] }}
                                    <x-frontend.icon name="chevron-down" class="h-5 w-5 text-muted transition-transform group-open:rotate-180" />
                                </summary>
                                <ul class="mb-2 ml-1 border-l border-line pl-4">
                                    @foreach ($item['children'] as $child)
                                        <li>
                                            <a href="{{ $child['url'] }}"
                                                class="block py-2 text-base text-ink-soft transition-colors hover:text-accent">
                                                {{ $child['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        @else
                            <a href="{{ $item['url'] }}"
                                @if ($item['new_tab']) target="_blank" rel="noopener" @endif
                                class="block py-3 font-display text-2xl text-ink transition-colors hover:text-accent">
                                {{ $item['label'] }}
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="space-y-3 border-t border-line px-6 py-6">
            <a href="{{ route('blog.index') }}" class="btn btn-outline w-full">Browse the journal</a>
            <a href="#newsletter" data-mobile-close class="btn btn-primary w-full">Subscribe</a>
        </div>
    </div>
</div>
