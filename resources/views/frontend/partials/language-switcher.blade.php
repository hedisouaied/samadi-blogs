@php
    $current = app()->getLocale();
    $class ??= '';
@endphp

<nav aria-label="{{ __('Language') }}" class="flex items-center gap-3 {{ $class }}">
    @foreach (supported_locales() as $code => $meta)
        @if ($code === $current)
            <span aria-current="true" lang="{{ $code }}" class="text-ink">{{ $meta['native'] }}</span>
        @else
            <a href="{{ route('locale.switch', $code) }}" lang="{{ $code }}" hreflang="{{ $code }}"
                class="link-underline hover:text-ink">{{ $meta['native'] }}</a>
        @endif
    @endforeach
</nav>
