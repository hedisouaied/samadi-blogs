@extends('frontend.layouts.app')

@section('content')
    <section class="shell pt-12 md:pt-16">
        <x-frontend.breadcrumbs :items="array_values(array_filter([
            ['label' => __('Home'), 'url' => route('home')],
            ['label' => __('Journal'), 'url' => route('blog.index')],
            $category->parent ? ['label' => $category->parent->name, 'url' => route('categories.show', $category->parent->slug)] : null,
            ['label' => $category->name],
        ]))" />

        <header class="mt-8 max-w-3xl">
            <p class="eyebrow eyebrow-accent">
                {{ $category->parent ? $category->parent->name : __('Section') }}
            </p>
            <h1 class="mt-3 font-display text-fluid-display text-ink">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="mt-5 text-lg leading-relaxed text-muted">{{ $category->description }}</p>
            @endif
            <p class="mt-5 text-xs uppercase tracking-[0.16em] text-muted">
                {{ trans_choice(':count article|:count articles', $articles->total(), ['count' => $articles->total()]) }}
            </p>
        </header>

        @if ($children->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($children as $child)
                    <a href="{{ route('categories.show', $child->slug) }}"
                        class="rounded-full border border-line px-4 py-2 text-sm text-ink-soft transition-colors hover:border-ink hover:text-ink">
                        {{ $child->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="shell mt-14 grid gap-14 lg:grid-cols-12 lg:gap-16">
        <div class="lg:col-span-8">
            @if ($articles->isNotEmpty())
                @if ($articles->onFirstPage() && $articles->count() > 1)
                    <div class="mb-14">
                        <x-frontend.article-card :article="$articles->first()" variant="feature" :priority="true" />
                    </div>
                @endif

                <div class="grid gap-x-8 gap-y-14 sm:grid-cols-2">
                    @foreach ($articles->skip($articles->onFirstPage() && $articles->count() > 1 ? 1 : 0) as $article)
                        <x-frontend.article-card :article="$article" variant="standard" :delay="($loop->index % 2) * 90" />
                    @endforeach
                </div>

                <div class="mt-16">
                    {{ $articles->links() }}
                </div>
            @else
                <x-frontend.empty-state icon="folder" :title="__('Nothing published in this section yet')"
                    :description="__('There are no articles here at the moment.')"
                    :action="route('blog.index')" :action-label="__('Browse the journal')" />
            @endif
        </div>

        <div class="lg:col-span-4">
            <x-frontend.blog-sidebar :categories="$categories" :trending="$trending" :tags="$tags" :active-category="$category" />
        </div>
    </section>
@endsection
