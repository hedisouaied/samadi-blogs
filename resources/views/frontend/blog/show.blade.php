@extends('frontend.layouts.app')

@section('progress')
    @include('frontend.partials.reading-progress')
@endsection

@section('content')
    <article class="pt-10 md:pt-14">

        <header class="shell">
            <x-frontend.breadcrumbs :items="[
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Journal', 'url' => route('blog.index')],
                ['label' => $article->title],
            ]" />

            <div class="mx-auto mt-10 max-w-4xl text-center">
                @if ($article->categories->first())
                    <a href="{{ route('categories.show', $article->categories->first()->slug) }}" class="tap-cat">
                        {{ $article->categories->first()->name }}
                    </a>
                @endif

                <h1 class="mt-4 font-display text-fluid-display text-ink">{{ $article->title }}</h1>

                @if ($article->excerpt)
                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-muted md:text-xl">{{ $article->excerpt }}</p>
                @endif

                <div class="mt-8 flex flex-wrap items-center justify-center gap-x-3 gap-y-2 text-[0.75rem] uppercase tracking-[0.12em] text-muted">
                    @if ($article->author)
                        <a href="{{ route('authors.show', $article->author->username ?: $article->author->getKey()) }}"
                            class="link-underline text-ink">{{ $article->author->name }}</a>
                        <span aria-hidden="true">/</span>
                    @endif
                    <time datetime="{{ $article->published_at?->toDateString() }}">{{ format_date($article->published_at) }}</time>
                    @if ($article->reading_time_minutes)
                        <span aria-hidden="true">/</span>
                        <span>{{ $article->reading_time_minutes }} min read</span>
                    @endif
                    @if ($article->view_count)
                        <span aria-hidden="true">/</span>
                        <span>{{ number_format($article->view_count) }} views</span>
                    @endif
                </div>
            </div>
        </header>

        @if ($article->featured_image_url)
            <figure class="shell mt-12 md:mt-16">
                <div class="media media-zoom mx-auto aspect-[16/9] max-w-5xl overflow-hidden rounded-2xl">
                    <img src="{{ $article->featured_image_url }}" alt="{{ $article->featured_image_alt ?: $article->title }}"
                        loading="eager" fetchpriority="high" decoding="async">
                </div>
                @if ($article->featured_image_alt)
                    <figcaption class="mx-auto mt-4 max-w-4xl text-center text-xs text-muted">{{ $article->featured_image_alt }}</figcaption>
                @endif
            </figure>
        @endif

        <div class="shell mt-16 grid gap-12 lg:grid-cols-12 lg:gap-10">

            <aside class="order-2 lg:order-1 lg:col-span-3">
                <div class="space-y-10 lg:sticky lg:top-28">
                    <x-frontend.toc :headings="$headings" />

                    <div class="hidden lg:block">
                        <x-frontend.share :article="$article" />
                    </div>
                </div>
            </aside>

            <div class="order-1 lg:order-2 lg:col-span-7">
                <div class="article-prose" data-article-body>
                    {!! $contentHtml !!}
                </div>

                @if ($inlineBanner)
                    <div class="my-14">
                        <x-frontend.banner :banner="$inlineBanner" variant="inline" />
                    </div>
                @endif

                @if ($article->tags->isNotEmpty())
                    <div class="mt-14 flex flex-wrap items-center gap-2 border-t border-line pt-8">
                        <span class="eyebrow mr-2">Filed under</span>
                        @foreach ($article->tags as $tag)
                            <x-frontend.tag-pill :tag="$tag" />
                        @endforeach
                    </div>
                @endif

                <div class="mt-8 lg:hidden">
                    <x-frontend.share :article="$article" />
                </div>
            </div>

            <aside class="order-3 lg:col-span-2">
                <div class="space-y-8 lg:sticky lg:top-28">
                    @if ($article->author)
                        <div class="rounded-2xl border border-line bg-surface p-5 text-center">
                            <span class="mx-auto flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-sunken font-display text-xl text-ink">
                                @if ($article->author->getFilamentAvatarUrl())
                                    <img src="{{ $article->author->getFilamentAvatarUrl() }}" alt="{{ $article->author->name }}"
                                        class="h-full w-full object-cover" loading="lazy" decoding="async">
                                @else
                                    {{ $article->author->initials }}
                                @endif
                            </span>
                            <p class="mt-4 text-sm font-medium text-ink">{{ $article->author->name }}</p>
                            @if ($article->author->bio)
                                <p class="mt-2 line-clamp-4 text-xs leading-relaxed text-muted">{{ $article->author->bio }}</p>
                            @endif
                            <a href="{{ route('authors.show', $article->author->username ?: $article->author->getKey()) }}"
                                class="link-underline mt-4 inline-block text-xs font-medium uppercase tracking-[0.12em] text-accent">
                                All articles
                            </a>
                        </div>
                    @endif

                    <div class="rounded-2xl border border-line bg-surface p-5 text-center">
                        <p class="text-3xl font-display text-ink">{{ $article->reading_time_minutes ?: 1 }}</p>
                        <p class="mt-1 text-[0.6875rem] uppercase tracking-[0.14em] text-muted">minute read</p>
                    </div>
                </div>
            </aside>
        </div>

        @if ($previous || $next)
            <div class="shell mt-20 grid gap-4 border-t border-line pt-12 sm:grid-cols-2">
                @if ($previous)
                    <a href="{{ route('blog.show', $previous->slug) }}" class="group rounded-2xl border border-line p-6 transition-colors hover:border-ink">
                        <span class="eyebrow flex items-center gap-2">
                            <x-frontend.icon name="arrow-left" class="h-3.5 w-3.5" /> Previous
                        </span>
                        <p class="card-title mt-3 text-lg leading-snug">{{ $previous->title }}</p>
                    </a>
                @else
                    <span></span>
                @endif

                @if ($next)
                    <a href="{{ route('blog.show', $next->slug) }}" class="group rounded-2xl border border-line p-6 text-right transition-colors hover:border-ink">
                        <span class="eyebrow flex items-center justify-end gap-2">
                            Next <x-frontend.icon name="arrow-right" class="h-3.5 w-3.5" />
                        </span>
                        <p class="card-title mt-3 text-lg leading-snug">{{ $next->title }}</p>
                    </a>
                @endif
            </div>
        @endif

        @if ($related->isNotEmpty())
            <section class="shell mt-20">
                <x-frontend.section-heading eyebrow="Keep reading" title="Related essays" :href="route('blog.index')" />
                <div class="mt-12 grid gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-frontend.article-card :article="$item" variant="standard" :delay="$loop->index * 90" />
                    @endforeach
                </div>
            </section>
        @endif

        <div class="shell mt-20">
            <div class="mx-auto max-w-3xl">
                <x-frontend.comments :article="$article" :comments="$comments" :enabled="$commentsEnabled" :count="$commentCount" />
            </div>
        </div>
    </article>
@endsection
