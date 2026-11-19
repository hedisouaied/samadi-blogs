@php
    $current = app()->getLocale();
    $locales = supported_locales();
@endphp

<x-filament::dropdown placement="bottom-end" teleport>
    <x-slot name="trigger">
        <button
            type="button"
            class="flex items-center gap-x-1 rounded-lg px-2 py-1.5 text-sm font-medium text-gray-600 outline-none transition hover:bg-gray-50 focus-visible:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:focus-visible:bg-white/5"
            aria-label="{{ __('Language') }}"
        >
            <x-filament::icon icon="heroicon-o-language" class="h-5 w-5" />
            <span>{{ strtoupper($current) }}</span>
        </button>
    </x-slot>

    <x-filament::dropdown.list>
        @foreach ($locales as $code => $meta)
            <x-filament::dropdown.list.item
                tag="a"
                :href="route('locale.switch', $code)"
                :color="$code === $current ? 'primary' : 'gray'"
                :icon="$code === $current ? 'heroicon-m-check' : null"
            >
                <span lang="{{ $code }}" dir="{{ $meta['dir'] }}">{{ $meta['native'] }}</span>
            </x-filament::dropdown.list.item>
        @endforeach
    </x-filament::dropdown.list>
</x-filament::dropdown>
