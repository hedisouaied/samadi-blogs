@inject('contentService', 'App\Services\PublicContentService')
@php
    $footerCategories = $contentService->categories()->take(6);
    $footerTags = $contentService->tags(10);
    $footerDescription = setting('general.site_description', '');
@endphp

<footer class="mt-24 border-t border-line bg-surface">
    <div class="shell py-16 md:py-24">
        <div class="grid gap-12 lg:grid-cols-12">

            <div class="lg:col-span-4">
                <a href="{{ route('home') }}" class="font-display text-3xl text-ink">{{ $siteName ?? setting('general.site_name') }}</a>
                @if ($footerDescription)
                    <p class="mt-5 max-w-sm text-sm leading-relaxed text-muted">{{ $footerDescription }}</p>
                @endif
                <div class="mt-6 flex items-center gap-3">
                    <a href="{{ route('feed') }}" class="icon-btn border border-line" aria-label="RSS feed">
                        <x-frontend.icon name="rss" class="h-4 w-4" />
                    </a>
                    <a href="{{ route('sitemap') }}" class="icon-btn border border-line" aria-label="Sitemap">
                        <x-frontend.icon name="compass" class="h-4 w-4" />
                    </a>
                    <a href="mailto:{{ setting('general.contact_email', 'hello@example.com') }}" class="icon-btn border border-line" aria-label="Email us">
                        <x-frontend.icon name="envelope" class="h-4 w-4" />
                    </a>
                </div>
            </div>

            <div class="lg:col-span-2">
                <h2 class="eyebrow">Sections</h2>
                <ul class="mt-5 space-y-3">
                    @foreach ($footerCategories as $category)
                        <li>
                            <a href="{{ route('categories.show', $category->slug) }}"
                                class="link-underline text-sm text-ink-soft transition-colors hover:text-ink">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-2">
                <h2 class="eyebrow">Topics</h2>
                <ul class="mt-5 space-y-3">
                    @foreach ($footerTags as $tag)
                        <li>
                            <a href="{{ route('tags.show', $tag->slug) }}"
                                class="link-underline text-sm text-ink-soft transition-colors hover:text-ink">
                                {{ $tag->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-4" id="newsletter">
                <h2 class="eyebrow">The newsletter</h2>
                <p class="mt-4 mb-5 text-sm leading-relaxed text-muted">
                    A considered digest of new essays and field notes, delivered occasionally. No noise.
                </p>
                <x-frontend.newsletter-form compact />
            </div>
        </div>
    </div>

    <div class="border-t border-line">
        <div class="shell flex flex-col items-center justify-between gap-4 py-6 text-xs text-muted md:flex-row">
            <p>&copy; {{ now()->year }} {{ $siteName ?? setting('general.site_name') }}. All rights reserved.</p>
            <nav aria-label="Footer meta" class="flex items-center gap-5">
                <a href="{{ route('blog.index') }}" class="link-underline hover:text-ink">Journal</a>
                <a href="{{ route('search') }}" class="link-underline hover:text-ink">Search</a>
                <a href="{{ url('/admin') }}" class="link-underline hover:text-ink">Admin</a>
            </nav>
        </div>
    </div>
</footer>
