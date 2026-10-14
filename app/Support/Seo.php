<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Builds the SEO payload consumed by the public layout.
 *
 * Values default to site settings and are merged with page-specific
 * overrides supplied by controllers.
 */
class Seo
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function make(array $overrides = []): array
    {
        $siteName = (string) setting('general.site_name', config('app.name'));
        $suffix = (string) setting('seo.seo_title_suffix', '');
        $title = (string) ($overrides['title'] ?? $siteName);

        $fullTitle = $title === $siteName || $suffix === ''
            ? $title
            : trim($title.' '.$suffix);

        $description = (string) ($overrides['description']
            ?? setting('seo.meta_description')
            ?? setting('general.site_description')
            ?? '');

        $canonical = (string) ($overrides['canonical'] ?? url()->current());

        $image = $overrides['image'] ?? self::storageUrl(setting('seo.social_image_path'));

        return array_merge([
            'title' => $title,
            'full_title' => $fullTitle,
            'description' => trim(strip_tags($description)),
            'canonical' => $canonical,
            'robots' => (string) ($overrides['robots'] ?? setting('seo.robots_policy', 'index,follow')),
            'type' => (string) ($overrides['type'] ?? 'website'),
            'image' => $image,
            'image_alt' => (string) ($overrides['image_alt'] ?? $title),
            'site_name' => $siteName,
            'locale' => str_replace('_', '-', (string) setting('general.default_language', 'en')),
            'published_at' => $overrides['published_at'] ?? null,
            'updated_at' => $overrides['updated_at'] ?? null,
            'author' => $overrides['author'] ?? null,
            'section' => $overrides['section'] ?? null,
            'tags' => $overrides['tags'] ?? [],
            'schema' => $overrides['schema'] ?? [],
            'prev' => $overrides['prev'] ?? null,
            'next' => $overrides['next'] ?? null,
        ], $overrides);
    }

    public static function storageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Storage::disk(config('filament.default_filesystem_disk', 'public'))->url($path);
    }

    /**
     * Build a BreadcrumbList structured-data node.
     *
     * @param  array<int, array{label: string, url?: string}>  $items
     * @return array<string, mixed>
     */
    public static function breadcrumbSchema(array $items): array
    {
        $elements = [];

        foreach (array_values($items) as $index => $item) {
            $element = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['label'],
            ];

            if (! empty($item['url'])) {
                $element['item'] = $item['url'];
            }

            $elements[] = $element;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }
}
