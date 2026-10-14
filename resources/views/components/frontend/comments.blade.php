@props(['article', 'comments', 'enabled' => true, 'count' => 0])

<section id="comments" class="scroll-mt-24">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line pb-6">
        <h2 class="font-display text-3xl text-ink md:text-4xl">Discussion</h2>
        <span class="eyebrow">{{ $count }} {{ \Illuminate\Support\Str::plural('comment', $count) }}</span>
    </div>

    @if ($enabled)
        <div class="mt-8 rounded-2xl border border-line bg-surface p-6 md:p-8">
            <h3 class="font-display text-xl text-ink">Join the conversation</p>
            <p class="mb-6 mt-1 text-sm text-muted">Be respectful and stay on topic.</p>
            <x-frontend.comment-form :article="$article" />
        </div>
    @else
        <p class="mt-8 rounded-2xl border border-line bg-surface px-6 py-8 text-center text-sm text-muted">
            Comments are closed for this article.
        </p>
    @endif

    <ol class="mt-12 space-y-10">
        @forelse ($comments as $comment)
            <x-frontend.comment-item :comment="$comment" :article="$article" />
        @empty
            <li class="text-center text-sm text-muted">Be the first to share a thought.</li>
        @endforelse
    </ol>
</section>
