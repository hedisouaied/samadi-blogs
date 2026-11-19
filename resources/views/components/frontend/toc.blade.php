@props(['headings' => []])

@if (count($headings))
    <nav data-toc aria-label="{{ __('On this page') }}" class="text-sm">
        <p class="eyebrow">{{ __('On this page') }}</p>
        <ul class="mt-4 space-y-0.5 border-s border-line">
            @foreach ($headings as $heading)
                <li>
                    <a href="#{{ $heading['id'] }}" data-toc-link data-level="{{ $heading['level'] }}"
                        class="toc-link -ms-px border-s border-transparent ps-4 hover:border-line-strong">
                        {{ $heading['text'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
