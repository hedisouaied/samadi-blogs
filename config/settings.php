<?php

use App\Enums\ArticleStatus;

/*
|--------------------------------------------------------------------------
| Application Settings Schema
|--------------------------------------------------------------------------
|
| Settings are stored one row per key in the `settings` table and merged
| with the defaults declared here. Add new settings by registering them
| in the relevant group below; the settings page and service pick them
| up automatically. Never store secrets here — use environment variables.
|
*/

return [

    'general' => [
        'label' => 'General',
        'description' => 'Core identity and localisation of the blog.',
        'icon' => 'heroicon-o-cog-6-tooth',
        'settings' => [
            'site_name' => ['label' => 'Site name', 'type' => 'string', 'default' => 'Samadi Blogs', 'required' => true, 'max' => 100],
            'site_tagline' => ['label' => 'Site tagline', 'type' => 'string', 'default' => 'Insights, stories and ideas', 'max' => 160],
            'site_description' => ['label' => 'Site description', 'type' => 'text', 'default' => 'A modern publication sharing practical articles and thoughtful analysis.', 'max' => 500],
            'contact_email' => ['label' => 'Contact email', 'type' => 'string', 'default' => 'hello@example.com', 'email' => true],
            'timezone' => ['label' => 'Timezone', 'type' => 'string', 'default' => 'UTC', 'helper' => 'PHP timezone identifier, e.g. UTC, Europe/Paris.'],
            'default_language' => ['label' => 'Default language', 'type' => 'string', 'default' => 'en', 'max' => 8],
            'date_format' => ['label' => 'Date format', 'type' => 'string', 'default' => 'M j, Y', 'helper' => 'Carbon/PHP date format used across the panel.'],
            'pagination_size' => ['label' => 'Default pagination size', 'type' => 'integer', 'default' => 15, 'min' => 5, 'max' => 100],
            'logo_path' => ['label' => 'Logo', 'type' => 'image', 'default' => null],
            'favicon_path' => ['label' => 'Favicon', 'type' => 'image', 'default' => null],
        ],
    ],

    'content' => [
        'label' => 'Content',
        'description' => 'Defaults applied when authoring articles and comments.',
        'icon' => 'heroicon-o-document-text',
        'settings' => [
            'default_article_status' => ['label' => 'Default article status', 'type' => 'select', 'default' => ArticleStatus::Draft->value, 'options' => ArticleStatus::class],
            'comment_moderation' => ['label' => 'Comment moderation', 'type' => 'select', 'default' => 'manual', 'options' => ['manual' => 'Manual approval', 'automatic' => 'Auto-approve', 'disabled' => 'Comments disabled']],
            'allow_comments_by_default' => ['label' => 'Allow comments by default', 'type' => 'boolean', 'default' => true],
            'reading_time_wpm' => ['label' => 'Reading speed (words / minute)', 'type' => 'integer', 'default' => 200, 'min' => 80, 'max' => 500],
            'homepage_articles_count' => ['label' => 'Homepage article count', 'type' => 'integer', 'default' => 9, 'min' => 3, 'max' => 30, 'helper' => 'Used by the future public front end homepage section.'],
            'default_author_id' => ['label' => 'Default author', 'type' => 'user', 'default' => null],
        ],
    ],

    'seo' => [
        'label' => 'SEO',
        'description' => 'Site-wide defaults for search engines and social sharing.',
        'icon' => 'heroicon-o-magnifying-glass',
        'settings' => [
            'seo_title_suffix' => ['label' => 'Title suffix', 'type' => 'string', 'default' => '— Samadi Blogs', 'max' => 100, 'helper' => 'Appended to article titles by the front end.'],
            'meta_description' => ['label' => 'Default meta description', 'type' => 'text', 'default' => 'A modern publication sharing practical articles and thoughtful analysis.', 'max' => 500],
            'social_image_path' => ['label' => 'Social sharing image', 'type' => 'image', 'default' => null],
            'canonical_base_url' => ['label' => 'Canonical base URL', 'type' => 'string', 'default' => 'http://localhost:8000', 'url' => true],
            'robots_policy' => ['label' => 'Default robots policy', 'type' => 'select', 'default' => 'index,follow', 'options' => ['index,follow' => 'index, follow', 'noindex,follow' => 'noindex, follow', 'index,nofollow' => 'index, nofollow', 'noindex,nofollow' => 'noindex, nofollow']],
        ],
    ],

    'newsletter' => [
        'label' => 'Newsletter',
        'description' => 'Subscriber defaults and consent wording for the future integration.',
        'icon' => 'heroicon-o-envelope',
        'settings' => [
            'newsletter_enabled' => ['label' => 'Enable subscriptions', 'type' => 'boolean', 'default' => true],
            'consent_wording' => ['label' => 'Consent wording', 'type' => 'text', 'default' => 'I agree to receive email updates and accept the privacy policy.', 'max' => 500],
            'confirmation_policy' => ['label' => 'Confirmation policy', 'type' => 'select', 'default' => 'double_opt_in', 'options' => ['single_opt_in' => 'Single opt-in', 'double_opt_in' => 'Double opt-in']],
        ],
    ],

    'media' => [
        'label' => 'Media',
        'description' => 'Upload limits and storage defaults for images and files.',
        'icon' => 'heroicon-o-photo',
        'settings' => [
            'max_upload_size_mb' => ['label' => 'Maximum upload size (MB)', 'type' => 'integer', 'default' => 5, 'min' => 1, 'max' => 50],
            'allowed_image_formats' => ['label' => 'Allowed image formats', 'type' => 'string', 'default' => 'jpg,jpeg,png,webp,gif', 'helper' => 'Comma separated list of extensions.'],
            'default_disk' => ['label' => 'Default image disk', 'type' => 'select', 'default' => 'public', 'options' => ['public' => 'Public (storage/app/public)', 'local' => 'Local (storage/app)']],
        ],
    ],

];
