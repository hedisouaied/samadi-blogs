@props([
    'id' => 'newsletter',
    'variant' => 'panel',
    'compact' => false,
])

@php
    $dark = $variant === 'dark';
    $fieldClass = $dark
        ? 'field !border-white/20 !bg-white/5 !text-white placeholder:!text-white/50'
        : 'field';
    $labelClass = $dark ? 'field-label !text-white/60' : 'field-label';
@endphp

<form id="{{ $id }}" data-newsletter action="{{ route('newsletter.subscribe') }}" method="post"
    class="w-full" novalidate>
    @csrf

    <div class="absolute h-0 w-0 overflow-hidden" aria-hidden="true">
        <label for="{{ $id }}-website">Leave this field empty</label>
        <input id="{{ $id }}-website" type="text" name="website" tabindex="-1" autocomplete="off">
    </div>

    <div class="space-y-3">
        @unless ($compact)
            <div>
                <label for="{{ $id }}-name" class="{{ $labelClass }}">Name <span class="normal-case tracking-normal text-muted">(optional)</span></label>
                <input id="{{ $id }}-name" name="name" type="text" autocomplete="name"
                    class="{{ $fieldClass }}" placeholder="Ada Lovelace">
            </div>
        @endunless

        <div>
            <label for="{{ $id }}-email" class="{{ $labelClass }}">Email address</label>
            <div class="flex flex-col gap-2 sm:flex-row">
                <input id="{{ $id }}-email" name="email" type="email" required autocomplete="email"
                    class="{{ $fieldClass }}" placeholder="you@example.com">
                <button type="submit"
                    class="btn {{ $dark ? '!bg-white !text-ink hover:!bg-accent hover:!text-accent-ink' : 'btn-primary' }} shrink-0">
                    <span data-newsletter-label>Subscribe</span>
                    <x-frontend.icon name="arrow-right" class="h-4 w-4" />
                </button>
            </div>
        </div>

        <label class="flex items-start gap-3 text-sm {{ $dark ? 'text-white/70' : 'text-muted' }}">
            <input type="checkbox" name="consent" value="1" required
                class="mt-0.5 h-4 w-4 shrink-0 rounded border-line-strong text-accent focus:ring-accent">
            <span>{{ setting('newsletter.consent_wording', 'I agree to receive email updates and accept the privacy policy.') }}</span>
        </label>
    </div>

    @error('email')
        <p class="mt-3 text-sm text-accent">{{ $message }}</p>
    @enderror

    <p data-newsletter-message role="status" aria-live="polite"
        class="mt-3 hidden text-sm {{ $dark ? 'text-white/80' : 'text-ink-soft' }}"></p>
</form>
