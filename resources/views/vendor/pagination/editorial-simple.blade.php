@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between border-t border-line pt-6">
        @if ($paginator->onFirstPage())
            <span class="btn btn-outline opacity-50">Newer</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline">Newer</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-ink">Older</a>
        @else
            <span class="btn btn-outline opacity-50">Older</span>
        @endif
    </nav>
@endif
