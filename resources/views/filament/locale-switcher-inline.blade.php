@php
    $current = app()->getLocale();
@endphp

<nav class="flex items-center justify-center gap-x-3 text-sm" aria-label="{{ __('Language') }}">
    @foreach (supported_locales() as $code => $meta)
        @if ($code === $current)
            <span class="font-semibold text-primary-600 dark:text-primary-400" lang="{{ $code }}">{{ $meta['native'] }}</span>
        @else
            <a href="{{ route('locale.switch', $code) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" lang="{{ $code }}">{{ $meta['native'] }}</a>
        @endif
    @endforeach
</nav>
