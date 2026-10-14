<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Str;

/**
 * Prepares stored article HTML for public rendering.
 *
 * Adds stable heading ids for deep linking and table-of-contents anchors,
 * hardens links and images, strips scripts, and returns the list of headings.
 */
class ArticleContent
{
    /**
     * @return array{html: string, headings: array<int, array{level: int, id: string, text: string}>}
     */
    public static function render(?string $html): array
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return ['html' => '', 'headings' => []];
        }

        $dom = new DOMDocument('1.0', 'UTF-8');

        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="article-content-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($dom);
        $root = $xpath->query('//*[@id="article-content-root"]')->item(0);

        if (! $root instanceof DOMElement) {
            return ['html' => $html, 'headings' => []];
        }

        foreach (iterator_to_array($xpath->query('.//script | .//style', $root)) as $node) {
            $node->parentNode?->removeChild($node);
        }

        $headings = self::assignHeadingIds($xpath, $root);
        self::hardenImages($xpath, $root);
        self::hardenLinks($xpath, $root);

        $output = '';

        foreach ($root->childNodes as $child) {
            $output .= (string) $dom->saveHTML($child);
        }

        return ['html' => $output, 'headings' => $headings];
    }

    /**
     * @return array<int, array{level: int, id: string, text: string}>
     */
    public static function headings(?string $html): array
    {
        return self::render($html)['headings'];
    }

    /**
     * @return array<int, array{level: int, id: string, text: string}>
     */
    protected static function assignHeadingIds(DOMXPath $xpath, DOMElement $root): array
    {
        $headings = [];
        $used = [];

        foreach ($xpath->query('.//h2 | .//h3', $root) as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $text = trim(preg_replace('/\s+/u', ' ', $node->textContent) ?? '');

            if ($text === '') {
                continue;
            }

            $base = Str::slug($text) ?: 'section';
            $id = $base;
            $suffix = 2;

            while (isset($used[$id])) {
                $id = $base.'-'.$suffix;
                $suffix++;
            }

            $used[$id] = true;
            $node->setAttribute('id', $id);

            $headings[] = [
                'level' => (int) substr($node->nodeName, 1),
                'id' => $id,
                'text' => $text,
            ];
        }

        return $headings;
    }

    protected static function hardenImages(DOMXPath $xpath, DOMElement $root): void
    {
        foreach ($xpath->query('.//img', $root) as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $node->setAttribute('loading', 'lazy');
            $node->setAttribute('decoding', 'async');

            if (! $node->hasAttribute('alt')) {
                $node->setAttribute('alt', '');
            }
        }
    }

    protected static function hardenLinks(DOMXPath $xpath, DOMElement $root): void
    {
        foreach ($xpath->query('.//a[@href]', $root) as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $href = $node->getAttribute('href');

            if (Str::startsWith($href, ['http://', 'https://'])) {
                $host = parse_url($href, PHP_URL_HOST);

                if ($host !== null && $host !== parse_url((string) config('app.url'), PHP_URL_HOST)) {
                    $node->setAttribute('target', '_blank');
                    $node->setAttribute('rel', 'noopener noreferrer');
                }
            }
        }
    }
}
