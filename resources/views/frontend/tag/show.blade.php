@extends('frontend.layouts.app')

@section('content')
    <section class="shell pt-12 md:pt-16">
        <x-frontend.breadcrumbs :items="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Journal', 'url' => route('blog.index')],
            ['label' => $tag->name],
        ]" />

        <header class="mt-8 max-w-3xl">
            <p class="eyebrow eyebrow-accent">Topic</p>
            <h1 class="mt-3 font-display text-fluid-display text-ink">#{{ $tag->name }}</h1>
            @if ($tag->description)
                <p class="mt-5 text-lg leading-relaxed text-muted">{{ $tag->description }}</p>
            @endif
            <p class="mt-5 text-xs uppercase tracking-[0.16em] text-muted">
                {{ $articles->total() }} {{ \Illuminate\Support\Str::plural('article', $articles->total()) }}
            </p>
        </header>
    </section>

    <section class="shell mt-14 grid gap-14 lg:grid-cols-12 lg:gap-16">
        <div class="lg:col-span-8">
            @if ($articles->isNotEmpty())
                <div class="grid gap-x-8 gap-y-14 sm:grid-cols-2">
                    @foreach ($articles as $article)
                        <x-frontend.article-card :article="$article" variant="standard" :delay="($loop->index % 2) * 90" :priority="$loop->first" />
                    @endforeach
                </div>

                <div class="mt-16">
                    {{ $articles->links() }}
                </div>
            @else
                <x-frontend.empty-state icon="tag" title="No articles with this topic"
                    description="Try another topic or browse the journal."
                    :action="route('blog.index')" action-label="Browse the journal" />
            @endif
        </div>

        <div class="lg:col-span-4">
            <x-frontend.blog-sidebar :categories="$categories" :trending="$trending" :tags="$tags" />
        </div>
    </section>
@endsection
