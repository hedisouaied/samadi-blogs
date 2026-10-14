@props(['headings' => []])

@if (count($headings))
    <nav data-toc aria-label="On this page" class="text-sm">
        <p class="eyebrow">On this page</p>
        <ul class="mt-4 space-y-0.5 border-l border-line">
            @foreach ($headings as $heading)
                <li>
                    <a href="#{{ $heading['id'] }}" data-toc-link data-level="{{ $heading['level'] }}"
                        class="toc-link -ml-px border-l border-transparent pl-4 hover:border-line-strong">
                        {{ $heading['text'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
