<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\ArticleStatus;
use App\Enums\Permission;
use App\Models\Article;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Article')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Content')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(200)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Set $set, Get $get): void {
                                        if ($operation === 'create' && blank($get('slug'))) {
                                            $set('slug', Str::slug((string) $state));
                                        }
                                    })
                                    ->columnSpanFull(),
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(220)
                                    ->alphaDash()
                                    ->unique(ignoreRecord: true)
                                    ->suffixAction(
                                        Action::make('regenerateSlug')
                                            ->icon('heroicon-o-arrow-path')
                                            ->tooltip('Regenerate from title')
                                            ->action(fn (Get $get, Set $set) => $set('slug', Str::slug((string) $get('title')))),
                                    )
                                    ->helperText('Public URL: /blog/{slug}')
                                    ->columnSpanFull(),
                                Textarea::make('excerpt')
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->helperText('Short summary shown in listings and social cards.')
                                    ->columnSpanFull(),
                                RichEditor::make('content')
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('articles/attachments')
                                    ->fileAttachmentsVisibility('public')
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make('Taxonomy')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Select::make('categories')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->helperText('Assign one or more categories.'),
                                Select::make('tags')
                                    ->relationship('tags', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')->required()->maxLength(80),
                                        TextInput::make('slug')->maxLength(100),
                                    ])
                                    ->native(false)
                                    ->helperText('Add or pick tags; new tags are created on the fly.'),
                            ]),

                        Tabs\Tab::make('Publishing')
                            ->icon('heroicon-o-rocket-launch')
                            ->schema([
                                Select::make('status')
                                    ->options(ArticleStatus::options())
                                    ->default(ArticleStatus::Draft->value)
                                    ->required()
                                    ->native(false)
                                    ->live()
                                    ->disabled(fn (): bool => ! Auth::user()?->hasPermission(Permission::PublishArticles))
                                    ->dehydrated()
                                    ->helperText(function (): string {
                                        return Auth::user()?->hasPermission(Permission::PublishArticles)
                                            ? 'Editors with publish rights can move an article through any status.'
                                            : 'You can save as Draft or submit for review; publishing is done by an editor.';
                                    }),
                                Select::make('author_id')
                                    ->label('Author')
                                    ->relationship('author', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->default(fn (): ?int => Auth::id())
                                    ->disabled(fn (): bool => ! Auth::user()?->hasPermission(Permission::UpdateAnyArticle))
                                    ->dehydrated()
                                    ->required(),
                                DateTimePicker::make('published_at')
                                    ->label('Publish date')
                                    ->seconds(false)
                                    ->native(false)
                                    ->visible(fn (Get $get): bool => $get('status') === ArticleStatus::Published->value)
                                    ->helperText('Leave empty to publish immediately when saved as Published.'),
                                DateTimePicker::make('scheduled_at')
                                    ->label('Scheduled for')
                                    ->seconds(false)
                                    ->native(false)
                                    ->required(fn (Get $get): bool => $get('status') === ArticleStatus::Scheduled->value)
                                    ->visible(fn (Get $get): bool => $get('status') === ArticleStatus::Scheduled->value),
                                Toggle::make('is_featured')->label('Featured article')->default(false)->inline(false),
                                Toggle::make('is_active')->label('Active')->default(true)->inline(false),
                                Toggle::make('allow_comments')->label('Allow comments')->default(true)->inline(false),
                            ]),

                        Tabs\Tab::make('Media')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                FileUpload::make('featured_image')
                                    ->label('Featured image')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                                    ->disk('public')
                                    ->directory('articles/featured')
                                    ->visibility('public')
                                    ->maxSize(6144),
                                TextInput::make('featured_image_alt')
                                    ->label('Featured image alt text')
                                    ->maxLength(180)
                                    ->helperText('Describe the image for accessibility and SEO.'),
                            ]),

                        Tabs\Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                TextInput::make('seo_title')
                                    ->maxLength(70)
                                    ->helperText('Defaults to the article title. Max 70 characters.')
                                    ->columnSpanFull(),
                                Textarea::make('seo_description')
                                    ->rows(2)
                                    ->maxLength(160)
                                    ->helperText('Meta description. Max 160 characters.')
                                    ->columnSpanFull(),
                                TextInput::make('canonical_url')
                                    ->label('Canonical URL')
                                    ->url()
                                    ->maxLength(255)
                                    ->helperText('Optional. Use when this content is duplicated elsewhere.'),
                                TextInput::make('reading_time_minutes')
                                    ->label('Reading time (minutes)')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Calculated on save'),
                                Toggle::make('seo_noindex')
                                    ->label('No-index this article')
                                    ->default(false)
                                    ->inline(false),
                            ]),
                    ]),

                Section::make()
                    ->visible(fn (?Article $record): bool => $record?->exists ?? false)
                    ->components([
                        TextInput::make('view_count')->label('Views')->disabled()->dehydrated(false),
                        TextInput::make('like_count')->label('Likes')->disabled()->dehydrated(false),
                        TextInput::make('share_count')->label('Shares')->disabled()->dehydrated(false),
                    ])
                    ->columns(3),
            ]);
    }
}
