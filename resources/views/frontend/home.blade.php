@extends('frontend.layouts.app')

@section('content')
    @if ($hero)
        <section class="shell pt-10 md:pt-14">
            <div class="grid gap-8 lg:grid-cols-12 lg:gap-10">
                <div class="lg:col-span-8">
                    <x-frontend.article-card :article="$hero" variant="hero" :priority="true" />
                </div>

                @if ($featured->isNotEmpty())
                    <div class="grid gap-6 sm:grid-cols-2 lg:col-span-4 lg:grid-cols-1">
                        @foreach ($featured as $item)
                            <x-frontend.article-card :article="$item" variant="overlay" :delay="$loop->index * 120" />
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @else
        <section class="shell pt-20">
            <x-frontend.empty-state icon="sparkles" :title="__('Welcome')"
                :description="__('Publish your first article in the admin panel and it will appear here.')" />
        </section>
    @endif

    <section class="shell mt-20 md:mt-28">
        <div class="grid gap-10 border-y border-line py-14 md:grid-cols-12 md:py-20">
            <div class="md:col-span-4">
                <p class="eyebrow eyebrow-accent">{{ __('The publication') }}</p>
            </div>
            <div class="md:col-span-8">
                <p class="font-display text-fluid-title text-ink">
                    {{ setting('general.site_description', 'A modern publication sharing practical articles and thoughtful analysis.') }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('blog.index') }}" class="btn btn-ink">
                        {{ __('Start reading') }}
                        <x-frontend.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                    <a href="#newsletter" class="btn btn-outline">{{ __('Join the newsletter') }}</a>
                </div>
            </div>
        </div>
    </section>

    @if ($latest->isNotEmpty())
        <section class="shell mt-20 md:mt-28">
            <x-frontend.section-heading :eyebrow="__('Latest')" :title="__('Fresh from the desk')"
                :href="route('blog.index')" :link-label="__('All articles')" />

            <div class="mt-12 grid gap-x-8 gap-y-14 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($latest as $article)
                    <x-frontend.article-card :article="$article" variant="standard" :delay="($loop->index % 3) * 90" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($middleBanner)
        <section class="shell mt-20 md:mt-28">
            <x-frontend.banner :banner="$middleBanner" />
        </section>
    @endif

    @if ($categories->isNotEmpty())
        <section class="shell mt-20 md:mt-28">
            <x-frontend.section-heading :eyebrow="__('Explore')" :title="__('Browse by section')" />

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories->take(6) as $category)
                    <x-frontend.category-card :category="$category" :delay="($loop->index % 3) * 90" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($trending->isNotEmpty())
        <section class="shell mt-20 md:mt-28">
            <div class="grid gap-12 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-7">
                    <x-frontend.section-heading :eyebrow="__('Reader favourites')" :title="__('Most read this month')" />
                    <ol class="mt-10 divide-y divide-line">
                        @foreach ($trending as $article)
                            <li class="py-6">
                                <x-frontend.article-card :article="$article" variant="list" :delay="$loop->index * 60" />
                            </li>
                        @endforeach
                    </ol>
                </div>

                <div class="lg:col-span-5">
                    <div class="rounded-3xl bg-ink p-8 text-bg md:p-12">
                        <p class="eyebrow !text-white/60">{{ __('Newsletter') }}</p>
                        <h2 class="mt-4 font-display text-3xl text-white md:text-4xl">{{ __('Read deeper, twice a month.') }}</h2>
                        <p class="mt-4 mb-8 text-sm leading-relaxed text-white/70">
                            {{ __('Essays, recommendations and notes from the desk — no noise, unsubscribe anytime.') }}
                        </p>
                        <x-frontend.newsletter-form variant="dark" :id="'home-newsletter'" />
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($authors->isNotEmpty())
        <section class="shell mt-20 md:mt-28">
            <x-frontend.section-heading :eyebrow="__('Contributors')" :title="__('The people behind the words')" />
            <div class="mt-14 grid gap-12 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($authors as $author)
                    <x-frontend.author-card :author="$author" :delay="$loop->index * 90" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($tags->isNotEmpty())
        <section class="shell mt-20 md:mt-28">
            <div class="rounded-3xl border border-line bg-surface p-8 md:p-12">
                <p class="eyebrow">{{ __('Topics') }}</p>
                <h2 class="mt-3 font-display text-2xl text-ink md:text-3xl">{{ __('Follow a thread') }}</h2>
                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach ($tags as $tag)
                        <x-frontend.tag-pill :tag="$tag" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
