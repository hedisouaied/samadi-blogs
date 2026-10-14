@if (session()->has('newsletter_status') || $errors->any())
    <div data-toast class="fixed inset-x-0 bottom-6 z-[90] flex justify-center px-4">
        <div class="flex items-center gap-3 rounded-full border px-5 py-3 text-sm shadow-2xl shadow-black/10 backdrop-blur
            {{ $errors->any() ? 'border-accent/40 bg-surface text-accent' : 'border-line bg-surface text-ink' }}">
            <x-frontend.icon :name="$errors->any() ? 'alert' : 'check'" class="h-4 w-4 shrink-0" />
            <span>{{ $errors->any() ? $errors->first() : session('newsletter_status') }}</span>
            <button type="button" data-toast-close aria-label="Dismiss" class="ml-2 text-muted transition-colors hover:text-ink">
                <x-frontend.icon name="close" class="h-4 w-4" />
            </button>
        </div>
    </div>
@endif
