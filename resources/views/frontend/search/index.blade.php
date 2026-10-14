@extends('frontend.layouts.app')

@section('content')
    <section class="shell pt-12 md:pt-16">
        <x-frontend.breadcrumbs :items="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Search'],
        ]" />

        <header class="mx-auto mt-10 max-w-3xl text-center">
            <p class="eyebrow eyebrow-accent">Search</p>
            <h1 class="mt-3 font-display text-fluid-display text-ink">Search the archive</h1>

            <form action="{{ route('search') }}" method="get" role="search" class="mt-8">
                <label for="page-search" class="sr-only">Search</label>
                <div class="relative">
                    <x-frontend.icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-muted" />
                    <input id="page-search" name="q" type="search" value="{{ $term }}" autofocus
                        placeholder="Search essays, topics and authors…"
                        class="w-full rounded-full border border-line-strong bg-surface py-4 pl-12 pr-32 text-base text-ink placeholder:text-muted focus:border-accent focus:outline-none focus:ring-4 focus:ring-accent/15">
                    <button type="submit" class="btn btn-primary absolute right-2 top-1/2 -translate-y-1/2">Search</button>
                </div>
            </form>
        </header>
    </section>

    <section class="shell mt-16">
        @if ($term === '')
            <div class="mx-auto max-w-3xl">
                <x-frontend.section-heading eyebrow="Browse" title="Explore by topic" />
                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach ($tags as $tag)
                        <x-frontend.tag-pill :tag="$tag" />
                    @endforeach
                </div>
                <div class="mt-12 grid gap-4 sm:grid-cols-2">
                    @foreach ($categories as $category)
                        <a href="{{ route('categories.show', $category->slug) }}"
                            class="group flex items-center justify-between rounded-xl border border-line px-5 py-4 transition-colors hover:border-ink">
                            <span class="font-display text-xl text-ink">{{ $category->name }}</span>
                            <x-frontend.icon name="arrow-right" class="h-4 w-4 text-muted transition-transform group-hover:translate-x-1" />
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <div class="mx-auto max-w-5xl">
                <p class="mb-10 text-sm text-muted">
                    @if ($results->total() > 0)
                        <span class="text-ink">{{ $results->total() }}</span>
                        {{ \Illuminate\Support\Str::plural('result', $results->total()) }} for
                    @else
                        No results for
                    @endif
                    &ldquo;{{ $term }}&rdquo;
                </p>

                @if ($results->isNotEmpty())
                    <div class="grid gap-x-8 gap-y-12 sm:grid-cols-2">
                        @foreach ($results as $article)
                            <x-frontend.article-card :article="$article" variant="standard" :delay="($loop->index % 2) * 90" />
                        @endforeach
                    </div>

                    <div class="mt-16">
                        {{ $results->links() }}
                    </div>
                @else
                    <x-frontend.empty-state icon="search" title="Nothing matched your search"
                        description="Try a different keyword, or browse the journal instead."
                        :action="route('blog.index')" action-label="Browse the journal" />
                @endif
            </div>
        @endif
    </section>
@endsection
