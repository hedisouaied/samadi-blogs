@props(['title' => 'Nothing here yet', 'description' => null, 'icon' => 'sparkles', 'action' => null, 'actionLabel' => null])

<div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-line px-6 py-20 text-center">
    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-sunken text-muted">
        <x-frontend.icon :name="$icon" class="h-6 w-6" />
    </span>
    <h2 class="mt-6 font-display text-2xl text-ink">{{ $title }}</h2>
    @if ($description)
        <p class="mt-3 max-w-md text-sm leading-relaxed text-muted">{{ $description }}</p>
    @endif
    @if ($action && $actionLabel)
        <a href="{{ $action }}" class="btn btn-outline mt-8">{{ $actionLabel }}</a>
    @endif
</div>
