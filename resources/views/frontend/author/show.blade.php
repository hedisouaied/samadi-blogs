@extends('frontend.layouts.app')

@section('content')
    <section class="shell pt-16 md:pt-20">
        <x-frontend.breadcrumbs :items="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Authors', 'url' => route('blog.index')],
            ['label' => $author->name],
        ]" />

        <header class="mt-10 flex flex-col items-center gap-6 text-center sm:flex-row sm:items-end sm:text-left">
            <span class="flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-full bg-sunken font-display text-4xl text-ink ring-1 ring-line">
                @if ($author->getFilamentAvatarUrl())
                    <img src="{{ $author->getFilamentAvatarUrl() }}" alt="{{ $author->name }}" class="h-full w-full object-cover">
                @else
                    {{ $author->initials }}
                @endif
            </span>

            <div class="flex-1">
                <p class="eyebrow eyebrow-accent">Author</p>
                <h1 class="mt-3 font-display text-fluid-display text-ink">{{ $author->name }}</h1>
                @if ($author->bio)
                    <p class="mt-4 max-w-2xl text-lg leading-relaxed text-muted">{{ $author->bio }}</p>
                @endif
                <p class="mt-5 text-xs uppercase tracking-[0.16em] text-muted">
                    {{ $articles->total() }} {{ \Illuminate\Support\Str::plural('article', $articles->total()) }}
                    @if ($author->username) &middot; &#64;{{ $author->username }} @endif
                </p>
            </div>
        </header>
    </section>

    <section class="shell mt-16">
        @if ($articles->isNotEmpty())
            <div class="grid gap-x-8 gap-y-14 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <x-frontend.article-card :article="$article" variant="standard" :delay="($loop->index % 3) * 90" :priority="$loop->first" />
                @endforeach
            </div>

            <div class="mt-16">
                {{ $articles->links() }}
            </div>
        @else
            <x-frontend.empty-state icon="user" title="No published articles yet"
                description="This author has not published anything yet."
                :action="route('blog.index')" action-label="Browse the journal" />
        @endif
    </section>
@endsection
