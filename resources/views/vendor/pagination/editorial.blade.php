@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination"
        class="flex flex-col items-center justify-between gap-6 border-t border-line pt-8 sm:flex-row">

        <div class="order-2 sm:order-1">
            @if ($paginator->onFirstPage())
                <span class="btn btn-outline pointer-events-none opacity-40">
                    <x-frontend.icon name="arrow-left" class="h-4 w-4" /> Newer
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline">
                    <x-frontend.icon name="arrow-left" class="h-4 w-4" /> Newer
                </a>
            @endif
        </div>

        <nav class="hidden items-center gap-1 md:flex" aria-label="Page numbers">
            @isset($elements)
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-3 text-sm text-muted">{{ $element }}</span>
                    @else
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                    class="flex h-10 min-w-10 items-center justify-center rounded-full bg-ink px-3 text-sm font-medium text-bg">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}"
                                    class="flex h-10 min-w-10 items-center justify-center rounded-full px-3 text-sm text-ink-soft transition-colors hover:bg-sunken hover:text-ink">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            @endisset
        </div>

        <p class="order-2 text-xs uppercase tracking-[0.16em] text-muted md:hidden">
            Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
        </p>

        <div class="order-3">
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-ink">
                    Older <x-frontend.icon name="arrow-right" class="h-4 w-4" />
                </a>
            @else
                <span class="btn btn-outline opacity-50">
                    Older <x-frontend.icon name="arrow-right" class="h-4 w-4" />
                </span>
            @endif
        </div>
    </nav>
@endif
