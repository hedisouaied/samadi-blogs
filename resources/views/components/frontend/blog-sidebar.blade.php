@props(['categories' => collect(), 'trending' => collect(), 'tags' => collect(), 'banner' => null, 'activeCategory' => null])

<aside class="space-y-12 lg:sticky lg:top-28">

    @if ($categories->isNotEmpty())
        <div class="rounded-2xl border border-line bg-surface p-6">
            <h2 class="eyebrow">Sections</h2>
            <ul class="mt-5 space-y-1">
                @foreach ($categories as $category)
                    <li>
                        <a href="{{ route('categories.show', $category->slug) }}"
                            class="group flex items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors hover:bg-sunken
                            {{ $activeCategory && $activeCategory->is($category) ? 'text-accent' : 'text-ink-soft hover:text-ink' }}">
                            <span>{{ $category->name }}</span>
                            @if ($category->published_articles_count)
                                <span class="text-xs text-muted">{{ $category->published_articles_count }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($trending->isNotEmpty())
        <div>
            <h2 class="eyebrow border-b border-line pb-4">Most read</h2>
            <ol class="mt-5 space-y-5">
                @foreach ($trending as $index => $article)
                    <li>
                        <x-frontend.article-card :article="$article" variant="minimal" :index="$index + 1" />
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

    @if ($banner)
        <x-frontend.banner :banner="$banner" variant="sidebar" />
    @endif

    @if ($tags->isNotEmpty())
        <div>
            <h2 class="eyebrow border-b border-line pb-4">Topics</h2>
            <div class="mt-5 flex flex-wrap gap-2">
                @foreach ($tags as $tag)
                    <x-frontend.tag-pill :tag="$tag" />
                @endforeach
            </div>
        </div>
    @endif

    <div class="rounded-2xl bg-ink p-6 text-bg">
        <p class="eyebrow !text-white/60">Newsletter</p>
        <h2 class="mt-3 font-display text-2xl text-white">Never miss an essay</h2>
        <p class="mt-2 mb-5 text-sm leading-relaxed text-white/70">A considered digest of new writing, occasionally.</p>
        <x-frontend.newsletter-form variant="dark" compact :id="'sidebar-newsletter'" />
    </div>
</aside>
