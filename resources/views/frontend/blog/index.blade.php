@extends('frontend.layouts.app')

@section('content')
    <section class="shell pt-12 md:pt-16">
        <x-frontend.breadcrumbs :items="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Journal'],
        ]" />

        <header class="mt-8 max-w-3xl">
            <p class="eyebrow eyebrow-accent">The journal</p>
            <h1 class="mt-3 font-display text-fluid-display text-ink">Essays &amp; field notes</h1>
            <p class="mt-5 text-lg leading-relaxed text-muted">
                Long-form reporting, ideas and observations — written to be read slowly.
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
                <x-frontend.empty-state icon="compass" title="The journal is quiet"
                    description="No articles have been published yet. Check back soon."
                    :action="route('home')" action-label="Back home" />
            @endif
        </div>

        <div class="lg:col-span-4">
            <x-frontend.blog-sidebar :categories="$categories" :trending="$trending" :tags="$tags" :banner="$middleBanner" />
        </div>
    </section>
@endsection
