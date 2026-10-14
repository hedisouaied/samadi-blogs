@props(['items' => []])

@if (count($items))
    <nav aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-2 text-[0.6875rem] uppercase tracking-[0.14em] text-muted">
            @foreach ($items as $item)
                <li class="flex items-center gap-2">
                    @if (! empty($item['url']) && ! $loop->last)
                        <a href="{{ $item['url'] }}" class="link-underline transition-colors hover:text-ink">{{ $item['label'] }}</a>
                    @else
                        <span class="{{ $loop->last ? 'text-ink' : '' }}">{{ $item['label'] }}</span>
                    @endif
                    @unless ($loop->last)
                        <span aria-hidden="true" class="text-line-strong">/</span>
                    @endunless
                </li>
            @endforeach
        </ol>
    </nav>
@endif
