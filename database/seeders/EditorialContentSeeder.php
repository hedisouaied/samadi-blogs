<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Enums\BannerPlacement;
use App\Enums\BannerStatus;
use App\Enums\CommentStatus;
use App\Enums\MenuItemType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Article;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\NewsletterSubscriber;
use App\Models\Tag;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EditorialContentSeeder extends Seeder
{
    /** @var array<string, User> */
    protected array $authors = [];

    /** @var array<string, Category> */
    protected array $categories = [];

    /** @var array<string, Tag> */
    protected array $tags = [];

    /** @var array<string, Article> */
    protected array $published = [];

    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $this->clearContent();
        $this->seedSettings();
        $this->seedAuthors();
        $this->seedCategories();
        $this->seedTags();
        $this->seedArticles();
        $this->seedComments();
        $this->seedBanners();
        $this->seedMenu();
        $this->seedSubscribers();
    }

    protected function clearContent(): void
    {
        Comment::query()->forceDelete();
        DB::table('article_category')->delete();
        DB::table('article_tag')->delete();
        Article::query()->forceDelete();
        MenuItem::query()->delete();
        Menu::query()->delete();
        Category::query()->forceDelete();
        Tag::query()->forceDelete();
        Banner::query()->forceDelete();
        NewsletterSubscriber::query()->delete();
    }

    protected function seedSettings(): void
    {
        app(SettingsService::class)->setMany([
            'general.site_name' => 'Chronicle',
            'general.site_tagline' => 'History & Stories',
            'general.site_description' => 'Long-form history, folklore and the stories that shaped us — a premium editorial archive of empires, ruins, manuscripts and memory.',
            'general.contact_email' => 'letters@chronicle.test',
            'general.timezone' => 'UTC',
            'general.default_language' => 'en',
            'general.date_format' => 'F j, Y',
            'general.pagination_size' => 9,
            'seo.seo_title_suffix' => '— Chronicle',
            'seo.meta_description' => 'A premium editorial archive of history and stories: empires, ancient worlds, lost cities, myth and the survival of ideas.',
            'seo.canonical_base_url' => 'http://localhost:8000',
            'seo.robots_policy' => 'index,follow',
            'content.comment_moderation' => 'manual',
            'content.allow_comments_by_default' => true,
            'content.homepage_articles_count' => 9,
            'newsletter.newsletter_enabled' => true,
            'newsletter.consent_wording' => 'I agree to receive the Chronicle newsletter and accept the privacy policy.',
            'newsletter.confirmation_policy' => 'double_opt_in',
        ]);
    }

    protected function seedAuthors(): void
    {
        $definitions = [
            2 => ['key' => 'elena', 'username' => 'elena.marchetti', 'name' => 'Elena Marchetti', 'email' => 'elena@chronicle.test', 'role' => UserRole::Editor, 'job_title' => 'Editor-in-Chief', 'bio' => 'Elena founded Chronicle after a decade reporting on heritage sites across the Mediterranean. She writes on empires, memory and the politics of the past.'],
            3 => ['key' => 'marcus', 'username' => 'marcus.hale', 'name' => 'Marcus Hale', 'email' => 'marcus@chronicle.test', 'role' => UserRole::Moderator, 'job_title' => 'Archaeology Correspondent', 'bio' => 'A field archaeologist turned writer, Marcus covers excavations, ruins and the slow work of reconstruction from the dig to the museum.'],
            4 => ['key' => 'amara', 'username' => 'amara.okonkwo', 'name' => 'Amara Okonkwo', 'email' => 'amara@chronicle.test', 'role' => UserRole::Author, 'job_title' => 'Historian', 'bio' => 'Amara studies pre-colonial African states and the archaeology of trade. She is writing a book on Great Zimbabwe and its interpreters.'],
            5 => ['key' => 'tomas', 'username' => 'tomas.beltran', 'name' => 'Tomás Beltrán', 'email' => 'tomas@chronicle.test', 'role' => UserRole::Author, 'job_title' => 'Contributing Writer', 'bio' => 'Tomás writes about cities, cartography and frontiers. He has wandered most of the Silk Road from Istanbul to Xi\'an, notebook in hand.'],
            6 => ['key' => 'iris', 'username' => 'iris.lindqvist', 'name' => 'Iris Lindqvist', 'email' => 'iris@chronicle.test', 'role' => UserRole::Author, 'job_title' => 'Folklore Editor', 'bio' => 'Iris collects oral traditions and the stories people tell about themselves. She edited a field guide to Norse folk belief.'],
            7 => ['key' => 'samuel', 'username' => 'samuel.okafor', 'name' => 'Samuel Okafor', 'email' => 'samuel@chronicle.test', 'role' => UserRole::Author, 'job_title' => 'Books & Letters', 'bio' => 'Samuel writes on libraries, scribes and the fragile survival of texts. His essays follow words as they travel between languages.'],
        ];

        foreach ($definitions as $id => $definition) {
            $user = User::withTrashed()->find($id);

            $attributes = [
                'username' => $definition['username'],
                'name' => $definition['name'],
                'email' => $definition['email'],
                'role' => $definition['role'],
                'status' => UserStatus::Active,
                'job_title' => $definition['job_title'],
                'bio' => $definition['bio'],
                'email_verified_at' => now(),
                'deleted_at' => null,
            ];

            if ($user) {
                $user->forceFill($attributes)->save();
            } else {
                $user = new User($attributes + ['password' => 'password']);
                $user->save();
            }

            $this->authors[$definition['key']] = $user->refresh();
        }
    }

    protected function seedCategories(): void
    {
        $definitions = [
            ['slug' => 'empires-conquest', 'name' => 'Empires & Conquest', 'description' => 'How power was won, held and lost — from Achaemenid Persia to the age of revolutions, and the people who paid for it.', 'image' => 'articles/cat-empires.jpg', 'color' => '#7c2233', 'sort' => 1, 'featured' => true],
            ['slug' => 'ancient-worlds', 'name' => 'Ancient Worlds', 'description' => 'Daily life, belief and technology in the oldest civilisations, read from their ruins, rubbish and records.', 'image' => 'articles/cat-ancient.jpg', 'color' => '#6c6a4c', 'sort' => 2, 'featured' => true],
            ['slug' => 'cities-ruins', 'name' => 'Cities & Ruins', 'description' => 'The rise, decay and rediscovery of the places people called home, and what survives after the people are gone.', 'image' => 'articles/cat-cities.jpg', 'color' => '#8a6d3b', 'sort' => 3, 'featured' => false],
            ['slug' => 'myth-folklore', 'name' => 'Myth & Folklore', 'description' => 'Gods, monsters and the stories cultures tell to explain themselves — and what changes when they are written down.', 'image' => 'articles/cat-myth.jpg', 'color' => '#46586a', 'sort' => 4, 'featured' => true],
            ['slug' => 'books-letters', 'name' => 'Books & Letters', 'description' => 'Manuscripts, libraries, scribes and translators — the fragile, human machinery that keeps ideas alive.', 'image' => 'articles/cat-books.jpg', 'color' => '#5c4a7d', 'sort' => 5, 'featured' => false],
            ['slug' => 'time-memory', 'name' => 'Time & Memory', 'description' => 'Archaeology, restoration and the politics of remembering: who decides what the past is allowed to mean.', 'image' => 'articles/cat-memory.jpg', 'color' => '#3f6b5c', 'sort' => 6, 'featured' => false],
        ];

        foreach ($definitions as $definition) {
            $category = Category::query()->create([
                'name' => $definition['name'],
                'slug' => $definition['slug'],
                'description' => $definition['description'],
                'image_path' => $definition['image'],
                'color' => $definition['color'],
                'sort_order' => $definition['sort'],
                'is_active' => true,
                'is_featured' => $definition['featured'],
                'show_in_menu' => true,
            ]);

            $this->categories[$definition['slug']] = $category;
        }

        $children = [
            ['slug' => 'lost-cities', 'name' => 'Lost Cities', 'parent' => 'cities-ruins', 'description' => 'Settlements abandoned, buried or forgotten — and the expeditions that found them again.', 'image' => 'articles/cat-cities.jpg', 'color' => '#8a6d3b', 'sort' => 7],
            ['slug' => 'manuscripts', 'name' => 'Manuscripts', 'parent' => 'books-letters', 'description' => 'Hand-copied books, marginalia and the scribes whose errors became history.', 'image' => 'articles/cat-books.jpg', 'color' => '#5c4a7d', 'sort' => 8],
        ];

        foreach ($children as $definition) {
            $category = Category::query()->create([
                'name' => $definition['name'],
                'slug' => $definition['slug'],
                'description' => $definition['description'],
                'image_path' => $definition['image'],
                'color' => $definition['color'],
                'parent_id' => $this->categories[$definition['parent']]->id,
                'sort_order' => $definition['sort'],
                'is_active' => true,
                'is_featured' => false,
                'show_in_menu' => true,
            ]);

            $this->categories[$definition['slug']] = $category;
        }
    }

    protected function seedTags(): void
    {
        $definitions = [
            ['slug' => 'ancient-egypt', 'name' => 'Ancient Egypt', 'color' => '#b8860b'],
            ['slug' => 'roman-empire', 'name' => 'Roman Empire', 'color' => '#7c2233'],
            ['slug' => 'archaeology', 'name' => 'Archaeology', 'color' => '#6c6a4c'],
            ['slug' => 'manuscripts', 'name' => 'Manuscripts', 'color' => '#5c4a7d'],
            ['slug' => 'silk-road', 'name' => 'Silk Road', 'color' => '#8a6d3b'],
            ['slug' => 'mythology', 'name' => 'Mythology', 'color' => '#46586a'],
            ['slug' => 'architecture', 'name' => 'Architecture', 'color' => '#7a5c3e'],
            ['slug' => 'oral-tradition', 'name' => 'Oral Tradition', 'color' => '#3f6b5c'],
            ['slug' => 'medieval', 'name' => 'Medieval', 'color' => '#6b4b3e'],
            ['slug' => 'preservation', 'name' => 'Preservation', 'color' => '#556b2f'],
            ['slug' => 'trade-routes', 'name' => 'Trade Routes', 'color' => '#8a6d3b'],
            ['slug' => 'lost-cities', 'name' => 'Lost Cities', 'color' => '#8a6d3b'],
        ];

        foreach ($definitions as $definition) {
            $tag = Tag::query()->create([
                'name' => $definition['name'],
                'slug' => $definition['slug'],
                'color' => $definition['color'],
                'is_active' => true,
            ]);

            $this->tags[$definition['slug']] = $tag;
        }
    }

    protected function seedArticles(): void
    {
        foreach ($this->articleDefinitions() as $definition) {
            $status = $definition['status'];

            $article = new Article([
                'title' => $definition['title'],
                'slug' => $definition['slug'],
                'excerpt' => $definition['excerpt'],
                'content' => $definition['content'],
                'featured_image' => $definition['image'],
                'featured_image_alt' => $definition['alt'],
                'status' => $status,
                'is_featured' => $definition['featured'] ?? false,
                'is_active' => true,
                'allow_comments' => true,
                'published_at' => $status === ArticleStatus::Published ? now()->subDays($definition['days_ago']) : null,
                'scheduled_at' => $status === ArticleStatus::Scheduled ? now()->addDays($definition['days_ahead'] ?? 6) : null,
                'seo_title' => $definition['seo_title'] ?? $definition['title'].' — Chronicle',
                'seo_description' => $definition['seo_description'] ?? $definition['excerpt'],
                'author_id' => $this->authors[$definition['author']]->id,
            ]);

            $article->save();

            $article->forceFill([
                'view_count' => $definition['views'] ?? 0,
                'like_count' => $definition['likes'] ?? 0,
                'share_count' => $definition['shares'] ?? 0,
            ])->save();

            $article->categories()->sync(
                collect($definition['categories'])->map(fn (string $slug): int => $this->categories[$slug]->id)->all()
            );

            $article->tags()->sync(
                collect($definition['tags'])->map(fn (string $slug): int => $this->tags[$slug]->id)->all()
            );

            $article->syncReadingTime();
            $article->save();

            if ($status === ArticleStatus::Published) {
                $this->published[$definition['slug']] = $article;
            }
        }
    }

    protected function seedComments(): void
    {
        $moderator = $this->authors['marcus'];

        $threads = [
            'how-the-silk-road-carried-more-than-silk' => [
                ['name' => 'Priya Raman', 'email' => 'priya@example.com', 'body' => 'The detail about paper reaching the Islamic world after Talas reframed the whole route for me. It is strange to think of a technology travelling faster than any army could have carried it.'],
                ['name' => 'Daniel Osei', 'email' => 'daniel@example.com', 'body' => 'Curious how much of the "Silk Road" idea is a nineteenth-century invention we now treat as timeless.', 'reply' => 'That is exactly the point of the piece — the name is younger than the last of the caravans.'],
            ],
            'mohenjo-daro-the-city-that-kept-its-drains' => [
                ['name' => 'Hannah Weiss', 'email' => 'hannah@example.com', 'body' => 'Sanitation ahead of nineteenth-century London is the kind of fact that quietly rearranges your sense of "progress".'],
                ['name' => 'Ravi Menon', 'email' => 'ravi@example.com', 'body' => 'I visited in 2019. The scale only makes sense when you are standing on the citadel looking down.'],
            ],
            'petra-the-city-carved-by-water-and-ambition' => [
                ['name' => 'Sofia Alvarez', 'email' => 'sofia@example.com', 'body' => 'The hydraulic argument is compelling. So much of what we call architecture is really water management with better marketing.'],
                ['name' => 'Julian Frost', 'email' => 'julian@example.com', 'body' => 'Would love a follow-up on the Nabataean inscriptions and what they still withhold.'],
                ['name' => 'Aiko Tanaka', 'email' => 'aiko@example.com', 'body' => 'Beautifully written. The desert scenes stayed with me.', 'reply' => 'Thank you, Aiko — Petra rewards slow reading.'],
            ],
            'magna-carta-was-a-failure-that-became-a-legend' => [
                ['name' => 'Gregory Shaw', 'email' => 'gregory@example.com', 'body' => 'Annulled within weeks by the pope, then resurrected as the foundation of rights it never contained. History has a sense of irony.'],
                ['name' => 'Mei Lin', 'email' => 'mei@example.com', 'body' => 'The point about later generations reading their own politics into it is important.'],
            ],
            'the-scribes-who-saved-the-classics' => [
                ['name' => 'Fatima Zahra', 'email' => 'fatima@example.com', 'body' => 'The idea that the classics survived through the errors of bored copyists is both humbling and oddly comforting.'],
                ['name' => 'Peter Novak', 'email' => 'peter@example.com', 'body' => 'Palimpsests are the original version control system.', 'reply' => 'And the recoveries are the original data forensics.'],
            ],
        ];

        foreach ($threads as $slug => $entries) {
            $article = $this->published[$slug] ?? null;

            if (! $article) {
                continue;
            }

            foreach ($entries as $entry) {
                $root = Comment::query()->create([
                    'article_id' => $article->id,
                    'author_name' => $entry['name'],
                    'author_email' => $entry['email'],
                    'content' => $entry['body'],
                    'status' => CommentStatus::Approved,
                    'approved_at' => now()->subDays(2),
                    'approved_by' => $moderator->id,
                ]);

                if (! empty($entry['reply'])) {
                    Comment::query()->create([
                        'article_id' => $article->id,
                        'parent_id' => $root->id,
                        'author_name' => 'Elena Marchetti',
                        'author_email' => $this->authors['elena']->email,
                        'content' => $entry['reply'],
                        'status' => CommentStatus::Approved,
                        'approved_at' => now()->subDay(),
                        'approved_by' => $moderator->id,
                    ]);
                }
            }
        }

        $pending = $this->published['the-terracotta-army-was-meant-to-be-invisible'] ?? null;

        if ($pending) {
            Comment::query()->create([
                'article_id' => $pending->id,
                'author_name' => 'Wei Chen',
                'author_email' => 'wei@example.com',
                'content' => 'I would push back slightly on the claim that the army was "meant to be invisible" — the pits were monumental in their own right. Happy to expand if you want a counterpoint.',
                'status' => CommentStatus::Pending,
            ]);
        }
    }

    protected function seedBanners(): void
    {
        $editor = $this->authors['elena'];

        Banner::query()->create([
            'title' => 'The Chronicle Dispatch',
            'subtitle' => 'History, twice a month',
            'description' => 'One long read and three shorter notes on the past, delivered every other Thursday. No noise, unsubscribe anytime.',
            'image_path' => 'articles/banner-ninth-wave.jpg',
            'alt_text' => 'A stormy seascape with a small vessel amid towering waves',
            'target_url' => '/#newsletter',
            'button_text' => 'Subscribe',
            'placement' => BannerPlacement::HomepageMiddle,
            'status' => BannerStatus::Published,
            'is_active' => true,
            'sort_order' => 1,
            'starts_at' => now()->subDays(20),
            'created_by' => $editor->id,
        ]);

        Banner::query()->create([
            'title' => 'From the Archive',
            'subtitle' => 'The Antikythera mechanism',
            'description' => 'A bronze geared device recovered from a shipwreck in 1901 — a mechanical model of the heavens built more than two thousand years ago.',
            'image_path' => 'articles/banner-antikythera.jpg',
            'alt_text' => 'The corroded bronze gears of the Antikythera mechanism',
            'button_text' => 'Read the archive',
            'placement' => BannerPlacement::ArticleInline,
            'status' => BannerStatus::Published,
            'is_active' => true,
            'sort_order' => 1,
            'starts_at' => now()->subDays(20),
            'created_by' => $editor->id,
        ]);
    }

    protected function seedMenu(): void
    {
        $menu = Menu::query()->create([
            'name' => 'Primary Navigation',
            'slug' => 'primary',
            'description' => 'Main site header navigation.',
            'is_active' => true,
        ]);

        $items = [
            ['label' => 'Home', 'type' => MenuItemType::CustomUrl, 'url' => '/', 'sort' => 0],
            ['label' => 'Empires & Conquest', 'slug' => 'empires-conquest', 'sort' => 1],
            ['label' => 'Ancient Worlds', 'slug' => 'ancient-worlds', 'sort' => 2],
            ['label' => 'Cities & Ruins', 'slug' => 'cities-ruins', 'sort' => 3],
            ['label' => 'Myth & Folklore', 'slug' => 'myth-folklore', 'sort' => 4],
            ['label' => 'Books & Letters', 'slug' => 'books-letters', 'sort' => 5],
        ];

        $parentItems = [];

        foreach ($items as $item) {
            $attributes = [
                'menu_id' => $menu->id,
                'label' => $item['label'],
                'sort_order' => $item['sort'],
                'is_active' => true,
            ];

            if (($item['type'] ?? null) === MenuItemType::CustomUrl) {
                $attributes['type'] = MenuItemType::CustomUrl;
                $attributes['url'] = $item['url'];
            } else {
                $attributes['type'] = MenuItemType::Category;
                $attributes['category_id'] = $this->categories[$item['slug']]->id;
            }

            $created = MenuItem::query()->create($attributes);
            $parentItems[$item['label']] = $created;
        }

        MenuItem::query()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parentItems['Cities & Ruins']->id,
            'label' => 'Lost Cities',
            'type' => MenuItemType::Category,
            'category_id' => $this->categories['lost-cities']->id,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        MenuItem::query()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parentItems['Books & Letters']->id,
            'label' => 'Manuscripts',
            'type' => MenuItemType::Category,
            'category_id' => $this->categories['manuscripts']->id,
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }

    protected function seedSubscribers(): void
    {
        $subscribed = [
            ['Priya Raman', 'priya.raman@example.com'],
            ['Daniel Osei', 'daniel.osei@example.com'],
            ['Hannah Weiss', 'hannah.weiss@example.com'],
            ['Ravi Menon', 'ravi.menon@example.com'],
            ['Sofia Alvarez', 'sofia.alvarez@example.com'],
            ['Julian Frost', 'julian.frost@example.com'],
            ['Aiko Tanaka', 'aiko.tanaka@example.com'],
            ['Gregory Shaw', 'gregory.shaw@example.com'],
            ['Mei Lin', 'mei.lin@example.com'],
            ['Fatima Zahra', 'fatima.zahra@example.com'],
            ['Peter Novak', 'peter.novak@example.com'],
            ['Wei Chen', 'wei.chen@example.com'],
        ];

        foreach ($subscribed as [$name, $email]) {
            NewsletterSubscriber::query()->create([
                'name' => $name,
                'email' => $email,
                'status' => 'subscribed',
                'source' => 'website',
                'subscribed_at' => now()->subDays(rand(1, 60)),
                'consent_at' => now()->subDays(rand(1, 60)),
                'consent_source' => 'newsletter_form',
            ]);
        }

        NewsletterSubscriber::query()->create([
            'name' => 'Louise Bernard',
            'email' => 'louise.bernard@example.com',
            'status' => 'pending',
            'source' => 'website',
            'consent_at' => now()->subDays(3),
            'consent_source' => 'newsletter_form',
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function articleDefinitions(): array
    {
        return [
            [
                'title' => 'How the Silk Road Carried More Than Silk',
                'slug' => 'how-the-silk-road-carried-more-than-silk',
                'author' => 'elena',
                'image' => 'articles/silk-road.jpg',
                'alt' => 'A camel caravan crossing desert dunes at dusk',
                'excerpt' => 'For centuries the greatest trade route in history moved ideas, faiths and plagues as readily as it moved silk. The story of the Silk Road is really the story of everything that travelled with the caravans.',
                'categories' => ['empires-conquest', 'books-letters'],
                'tags' => ['silk-road', 'trade-routes', 'medieval'],
                'featured' => true,
                'status' => ArticleStatus::Published,
                'days_ago' => 18,
                'views' => 4210,
                'likes' => 214,
                'shares' => 51,
                'content' => <<<'HTML'
<p>There was never a single road called the Silk Road. The name was coined in 1877 by the German geographer Ferdinand von Richthofen, long after the network of steppe, desert and sea routes it described had already risen and fallen several times. What connected Chang'an to Samarkand, and Samarkand to Baghdad and Constantinople, was not pavement but trust: a chain of oases, caravanserais and merchant families who agreed, for a price, to carry goods they would never see to people they would never meet.</p>

<h2>What actually moved</h2>
<p>Silk travelled west; horses, glass and wool travelled east. But the most valuable cargo was weightless. Paper, developed in China, reached the Islamic world after the Battle of Talas in 751 and Europe centuries later. Buddhism walked the northern routes into Central Asia; Manichaeism, Nestorian Christianity and later Islam moved along the same tracks. So did mathematics: the numerals we still call "Arabic" were refined in the very cities the caravans fed.</p>

<h3>A route made of cities, not roads</h3>
<p>The network was really a crowded sequence of urban oases — Merv, Bukhara, Samarkand, Kashgar — each a small world with its own languages, coinage and gods. A merchant rarely travelled the whole length. Goods were handed on, and with them reputations, recipes and rumours.</p>

<h2>The price of connection</h2>
<p>Connection had a cost. The same routes that carried monastic libraries also carried the plague. The Black Death of the fourteenth century followed the caravan and sea lanes with terrible efficiency, reaching the Mediterranean from the steppe within a few years.</p>

<blockquote>To study the Silk Road is to study how the world became small long before it became fast.</blockquote>

<p>When sea routes opened and empires shifted, the overland cities emptied almost as quickly as they had grown. Their ruins — and their multilingual libraries — are what we might now call the first draft of globalisation.</p>
HTML,
            ],
            [
                'title' => 'The Rosetta Stone and the Argument That Unlocked Egypt',
                'slug' => 'the-rosetta-stone-and-the-argument-that-unlocked-egypt',
                'author' => 'samuel',
                'image' => 'articles/rosetta-stone.jpg',
                'alt' => 'The Rosetta Stone, its surface covered in three scripts',
                'excerpt' => 'A slab of granodiorite found by soldiers in 1799 gave scholars the same decree in three scripts — and turned a mystery into an argument that lasted a generation.',
                'categories' => ['ancient-worlds', 'books-letters'],
                'tags' => ['ancient-egypt', 'manuscripts', 'archaeology'],
                'status' => ArticleStatus::Published,
                'days_ago' => 30,
                'views' => 3890,
                'likes' => 178,
                'shares' => 44,
                'content' => <<<'HTML'
<p>In July 1799, French soldiers rebuilding a fort at Rosetta in the Nile Delta noticed a slab of dark stone built into an old wall. It carried three bands of writing: hieroglyphs at the top, a cursive script in the middle, and Greek at the bottom. The Greek could be read. Everyone understood, almost at once, what the stone might mean.</p>

<h2>A decree, repeated three times</h2>
<p>The text was a decree issued at Memphis in 196 BC on behalf of Ptolemy V. Priests ordered it copied into sacred hieroglyphs, the everyday demotic script and Greek, so that it could be read by everyone it concerned. It was bureaucratic repetition — and it became the key to a civilisation.</p>

<h2>Two rivals and a slow breakthrough</h2>
<p>The English polymath Thomas Young established that cartouches enclosed royal names and that hieroglyphs could represent sounds as well as ideas. The French scholar Jean-François Champollion went further, realising that the script was a mixed system and that the names of foreign rulers spelled out sounds rather than meanings alone. In 1822 he announced that he could read the names of kings.</p>

<p>It would be tidy to say the stone was "translated" by one genius in one afternoon. It was not. It was won slowly, through rivalry, error and the stubborn comparison of texts.</p>

<h2>Why it still matters</h2>
<p>Decipherment did not just reveal a language; it restored three thousand years of ordinary life — tax receipts, love poems, medical recipes and complaints about lazy workers. Egypt stopped being a landscape of mysterious symbols and became a place where people argued, worshipped and kept accounts.</p>
HTML,
            ],
            [
                'title' => 'Mohenjo-daro: The City That Kept Its Drains',
                'slug' => 'mohenjo-daro-the-city-that-kept-its-drains',
                'author' => 'amara',
                'image' => 'articles/mohenjo-daro.jpg',
                'alt' => 'Excavated brick streets and walls of an ancient city',
                'excerpt' => 'Four and a half thousand years ago an Indus city planned its streets on a grid, covered its drains, and left behind a script nobody in the world has yet learned to read.',
                'categories' => ['cities-ruins', 'ancient-worlds'],
                'tags' => ['archaeology', 'architecture'],
                'featured' => true,
                'status' => ArticleStatus::Published,
                'days_ago' => 12,
                'views' => 2140,
                'likes' => 143,
                'shares' => 29,
                'content' => <<<'HTML'
<p>Mohenjo-daro, "the mound of the dead", sits in the Sindh province of modern Pakistan. It was built around 2500 BC, flourished for perhaps seven centuries, and was abandoned long before anyone thought to name it. What its builders left is a city laid out on a grid, with bricks of a single standard size, streets aligned to the cardinal points, and a covered drainage system that would embarrass many cities built four thousand years later.</p>

<h2>The city as infrastructure</h2>
<p>The citadel stood on a raised platform of mud brick, separate from the lower town where most people lived. Wells were sunk into almost every block. Waste water ran from houses into covered channels beneath the streets, with inspection holes at intervals — the kind of detail that implies maintenance, rules and someone whose job it was to clear the drains.</p>

<h3>A city without obvious palaces</h3>
<p>Unlike Egypt or Mesopotamia, the Indus cities yield few signs of kings or monumental temples. The largest structures, like the so-called Great Bath, are public in character. The evidence hints at a society organised by trade, craft and shared custom rather than by a single remembered ruler.</p>

<h2>The script that stayed silent</h2>
<p>Thousands of small seals carry a script of some four hundred signs. It appears on jewellery, tools and pottery, which suggests it was widespread. It has never been deciphered, and it may never be, because the longest known inscription is only a handful of characters. Until then, the people of Mohenjo-daro keep their opinions to themselves.</p>
HTML,
            ],
            [
                'title' => 'Petra: The City Carved by Water and Ambition',
                'slug' => 'petra-the-city-carved-by-water-and-ambition',
                'author' => 'tomas',
                'image' => 'articles/petra.jpg',
                'alt' => 'The carved stone facade of a monument at Petra',
                'excerpt' => 'The rose-red city in the Jordanian desert was not only a wonder of stone: it was a triumph of hydraulics, built by a people who made the desert pay to be crossed.',
                'categories' => ['cities-ruins'],
                'tags' => ['lost-cities', 'architecture'],
                'featured' => true,
                'status' => ArticleStatus::Published,
                'days_ago' => 40,
                'views' => 3020,
                'likes' => 167,
                'shares' => 38,
                'content' => <<<'HTML'
<p>Petra is usually photographed from the same spot: the view through the Siq, the narrow gorge that opens suddenly onto the Treasury's carved facade. The image is deserved and misleading, because it makes Petra look like a single monument rather than a working city that once controlled the incense and spice trade between Arabia and the Mediterranean.</p>

<h2>Stone built for water, not for show</h2>
<p>The Nabataeans who built Petra were masters of water in a landscape that offered almost none. They cut channels and cisterns, dams and terraces, gathering winter rain and flash floods and holding them through the dry season. The famous facades are spectacular, but the engineering that made them survivable is the more impressive achievement.</p>

<h2>A city of merchants and gods</h2>
<p>Petra sat at the meeting of routes and turned that position into wealth. Its people minted their own coins, welcomed caravans and absorbed influences from Greece, Egypt and Rome while keeping a distinctly Nabataean identity. Their temples and tombs mix styles with confident ease.</p>

<h3>Decline and rediscovery</h3>
<p>As trade shifted to sea routes and the region came under Roman control, Petra's advantage faded. Earthquakes in the fourth and sixth centuries damaged what prosperity had built. For more than a thousand years it was known only to local communities, until the Swiss traveller Johann Ludwig Burckhardt reached it in 1812. Even then it took a century more for the world to understand how much of the city still lay under the sand.</p>
HTML,
            ],
            [
                'title' => 'Great Zimbabwe: The Stone Circles Europe Refused to Believe',
                'slug' => 'great-zimbabwe-the-stone-circles-europe-refused-to-believe',
                'author' => 'amara',
                'image' => 'articles/great-zimbabwe.jpg',
                'alt' => 'Curved dry-stone walls of an ancient African city',
                'excerpt' => 'A thousand years ago, masons working without mortar raised walls eleven metres high. For decades colonial scholars insisted someone else must have built them.',
                'categories' => ['cities-ruins', 'time-memory'],
                'tags' => ['archaeology', 'preservation'],
                'status' => ArticleStatus::Published,
                'days_ago' => 9,
                'views' => 1780,
                'likes' => 205,
                'shares' => 63,
                'content' => <<<'HTML'
<p>Great Zimbabwe gives its name to the modern nation, and yet its history was, for a long time, deliberately obscured. Between roughly 1100 and 1450, the ancestors of the Shona people built a city of dry-stone walls on a granite plateau — mortarless courses of shaped stone, curved like the contours of the land, with walls up to eleven metres high and some five metres thick at the base.</p>

<h2>Wealth measured in cattle, gold and cloth</h2>
<p>The city was the centre of a powerful state that controlled trade in gold, ivory and cattle, and reached outward through the Indian Ocean to the Swahili coast, where imported Persian and Chinese goods have been recovered from its ruins. Its population at its height may have numbered in the tens of thousands.</p>

<h2>The politics of denial</h2>
<p>When European travellers reached the site in the nineteenth century, many refused to accept that it had been built by Africans. Colonial authorities discouraged excavation, removed artefacts and promoted theories of a lost foreign civilisation. Only in the twentieth century did disciplined archaeology, and later the recovery of independence, restore the obvious: Great Zimbabwe was built by the ancestors of the people who still live around it.</p>

<blockquote>Archaeology can be evidence, but it can also be a battleground.</blockquote>

<p>The site is now a national monument and a UNESCO World Heritage Site. Its walls have become a symbol not only of a medieval kingdom, but of the struggle to control who is allowed to remember it.</p>
HTML,
            ],
            [
                'title' => 'The Library of Alexandria Was Never Just One Building',
                'slug' => 'the-library-of-alexandria-was-never-just-one-building',
                'author' => 'samuel',
                'image' => 'articles/library-alexandria.jpg',
                'alt' => 'Ancient ruins with columns beside the sea',
                'excerpt' => 'The great library of the ancient world is usually described as a thing that burned. It was really a centuries-long project, and its decline had more to do with money and politics than with fire.',
                'categories' => ['books-letters'],
                'tags' => ['manuscripts', 'preservation'],
                'status' => ArticleStatus::Published,
                'days_ago' => 25,
                'views' => 2650,
                'likes' => 156,
                'shares' => 41,
                'content' => <<<'HTML'
<p>The image is irresistible: shelves of scrolls, a torch, a single night of destruction. But the Library of Alexandria was never a single building with a single fate. Founded under the early Ptolemies in the third century BC, it was part of a wider scholarly project — a mouseion, or "place of the Muses" — supported by royal money and staffed by salaried researchers.</p>

<h2>A project, not a shelf</h2>
<p>The library's ambition was total: to collect a copy of every book in the world. Ships entering the harbour were searched for scrolls, which were copied — sometimes, reportedly, with the original kept and the copy returned. Scholars catalogued, argued and compiled, producing the first serious work in fields from geography to literary criticism.</p>

<h2>A long, slow decline</h2>
<p>No single catastrophe ended it. Scholars fell out, patronage was cut, and Rome interfered in Egyptian politics. A fire during Julius Caesar's campaign in 48 BC damaged part of the collection. The royal patronage that sustained an institution without a market eventually failed. By late antiquity the library had fragmented, its texts scattered or lost through neglect and repeated upheaval.</p>

<h3>What was actually lost</h3>
<p>We cannot count the books that disappeared, precisely because they are gone. What survives — fragments, quotations and titles in other people's catalogues — hints at a vast literature we can only infer. The library is less a cautionary tale about fire than about how quickly a civilisation's memory depends on who is willing to keep paying for it.</p>
HTML,
            ],
            [
                'title' => 'Magna Carta Was a Failure That Became a Legend',
                'slug' => 'magna-carta-was-a-failure-that-became-a-legend',
                'author' => 'elena',
                'image' => 'articles/magna-carta.jpg',
                'alt' => 'A medieval manuscript with closely written Latin script',
                'excerpt' => 'The charter sealed at Runnymede in 1215 was annulled within weeks. Its afterlife as a foundation of liberty tells us more about later politics than about King John.',
                'categories' => ['empires-conquest', 'time-memory'],
                'tags' => ['medieval', 'preservation'],
                'status' => ArticleStatus::Published,
                'days_ago' => 55,
                'views' => 3340,
                'likes' => 188,
                'shares' => 47,
                'content' => <<<'HTML'
<p>In June 1215 a group of rebellious barons forced King John to seal a charter at Runnymede, on the Thames. It was a peace treaty after a civil war, hastily drafted, full of narrow clauses about feudal dues, widow's inheritances and royal forests. Within weeks it had collapsed; the pope annulled it and the war resumed. As a practical document, it failed.</p>

<h2>Why it survived anyway</h2>
<p>Reissued several times in the thirteenth century under a new king, the charter took on a life of its own. Because it was reissued, copied and stored, it became a reference point — a written promise that even the king was subject to law. Clauses that mattered little in 1215 acquired enormous weight when read centuries later.</p>

<h2>The making of a myth</h2>
<p>Seventeenth-century lawyers fighting the Stuart kings reached back to Magna Carta and read into it rights its authors never imagined. Eighteenth-century radicals carried the idea across the Atlantic. "Liberty" clauses that once protected a handful of barons became, by reinterpretation, statements about everyone.</p>

<p>This is not to say the charter was a forgery. It is to say that documents, like people, are remembered for who they became. The historical Magna Carta concerned privilege; the remembered one concerns rights. Both are true, and the distance between them is the story.</p>
HTML,
            ],
            [
                'title' => 'The Terracotta Army Was Meant to Be Invisible',
                'slug' => 'the-terracotta-army-was-meant-to-be-invisible',
                'author' => 'marcus',
                'image' => 'articles/terracotta-army.jpg',
                'alt' => 'Rows of life-sized terracotta soldier statues',
                'excerpt' => 'Eight thousand figures were buried with the first emperor of China to guard him in death. They were never meant for human eyes — which makes their faces all the stranger.',
                'categories' => ['ancient-worlds'],
                'tags' => ['archaeology'],
                'status' => ArticleStatus::Published,
                'days_ago' => 6,
                'views' => 1520,
                'likes' => 97,
                'shares' => 22,
                'content' => <<<'HTML'
<p>In 1974 farmers digging a well near Xi'an struck something hard and, eventually, something extraordinary: a pit filled with life-sized soldiers made of terracotta, standing in neat ranks underground. Since then, excavation has revealed three main pits containing thousands of figures — infantry, archers, charioteers and horses — buried to guard the tomb of Qin Shi Huang, the first emperor of a unified China.</p>

<h2>An army built to be unseen</h2>
<p>The figures were not a monument for the living. They lie in chambers sealed beneath the earth as part of a tomb complex designed for the afterlife. To their makers, visibility was beside the point. Their realism served the dead emperor, not a future audience.</p>

<h3>Faces without a single model</h3>
<p>Each face appears individual, as if sculpted from a distinct person. The bodies were mass-produced from standard moulds, but the heads and facial features were finished separately, giving an impression of an army of specific men. The effect is uncanny precisely because it was directed at no viewer at all.</p>

<h2>What the pits still conceal</h2>
<p>The most intriguing question is unanswered by choice: the emperor's own burial chamber is supposed to lie beneath a mound nearby, and no one has opened it. Ancient sources warn of traps and rivers of mercury. Modern tests have detected higher-than-expected mercury in the surrounding soil. For now, the greatest discovery may still be waiting under the earth — still, as intended, hidden.</p>
HTML,
            ],
            [
                'title' => 'Why Every God Has Two Stories',
                'slug' => 'why-every-god-has-two-stories',
                'author' => 'iris',
                'image' => 'articles/comparative-mythology.jpg',
                'alt' => 'A mythological scene painted in rich colour',
                'excerpt' => 'There is the story a culture tells about its gods, and the story scholars tell about the story. The gap between them is where mythology actually lives.',
                'categories' => ['myth-folklore'],
                'tags' => ['mythology', 'oral-tradition'],
                'status' => ArticleStatus::Published,
                'days_ago' => 21,
                'views' => 2010,
                'likes' => 132,
                'shares' => 34,
                'content' => <<<'HTML'
<p>Ask someone to tell you a myth and they will usually give you a plot: a flood, a theft of fire, a descent into the underworld. Ask a historian of religion and you will get a second story, about how the first one changed as it moved between languages, was written down, edited and translated. Both are true, and mythology lives in the tension between them.</p>

<h2>The story as it is told</h2>
<p>In oral tradition a myth is never fixed. The teller adjusts it to the audience, the season and the occasion. Meaning lives in performance — in pauses, gestures and what is left unsaid. A god who is fearsome in one telling may be comic in another, because the story is doing different work.</p>

<h2>The story after it is written</h2>
<p>Once a myth is written down it hardens. A single scribe's version becomes "the" version. Later readers treat it as scripture or literature rather than as living practice. What we call classical mythology is often the moment a fluid tradition was photographed mid-motion.</p>

<h3>Shared shapes, local meanings</h3>
<p>Flood stories, dying and rising gods and journeys to the land of the dead appear across cultures that never met. It is tempting to see one original tale behind them all. The safer reading is that human societies face the same problems — death, hunger, weather, war — and reach for the same narrative tools. Similar shapes, very different meanings.</p>

<p>To study myth is not to debunk it. It is to notice that people need stories the way they need maps, and that the story about the story is part of the inheritance.</p>
HTML,
            ],
            [
                'title' => 'The Vikings Brought Their Stories With Them',
                'slug' => 'the-vikings-brought-their-stories-with-them',
                'author' => 'iris',
                'image' => 'articles/viking-age.jpg',
                'alt' => 'A carved Viking-era stone monument',
                'excerpt' => 'The Norse who raided and traded across Europe and the North Atlantic carried more than weapons. They carried poems, laws and gods that had to survive the sea.',
                'categories' => ['myth-folklore', 'empires-conquest'],
                'tags' => ['oral-tradition', 'medieval'],
                'status' => ArticleStatus::Published,
                'days_ago' => 34,
                'views' => 2450,
                'likes' => 149,
                'shares' => 37,
                'content' => <<<'HTML'
<p>The word "Viking" describes an activity more than a people: raiding, trading and settling overseas, roughly between the eighth and eleventh centuries. The Norse who left Scandinavia for Britain, Ireland, Iceland, Greenland and beyond were not a single nation. But they carried a shared inheritance of story, law and belief, and those travelled with them across the water.</p>

<h2>Memory held in verse</h2>
<p>Much of what we call Norse mythology reached us through texts written down in Christian Iceland, centuries after the events they describe. Poets preserved the older material in dense, allusive verse that assumed an audience already knew the stories. The written record is therefore a late echo of a long oral tradition.</p>

<h2>Gods of a hard landscape</h2>
<p>Norse gods do not promise comfort. They are powerful, flawed and destined to lose at Ragnarök. That fatalism has been read as a reflection of a world of short growing seasons, dangerous seas and uncertain luck — a religion of people who expected the world to be difficult and valued courage anyway.</p>

<h3>Stories as portable property</h3>
<p>When families sailed to Iceland or Greenland, they carried their poems, genealogies and laws in memory. In a new land without libraries, the story was the archive. The sagas that later emerged from Iceland are the long consequence of that habit of remembering.</p>

<p>To meet a Viking today, in the popular imagination, is to meet a helmeted raider. To read the sources is to meet something stranger and more human: a settler, a poet and a lawyer, carrying a difficult inheritance across a cold sea.</p>
HTML,
            ],
            [
                'title' => 'Samarkand: The City That Kept Rebuilding Its Own Dream',
                'slug' => 'samarkand-the-city-that-kept-rebuilding-its-own-dream',
                'author' => 'tomas',
                'image' => 'articles/samarkand.jpg',
                'alt' => 'Turquoise-tiled madrasahs around a Central Asian square',
                'excerpt' => 'Conquered, razed and rebuilt more than once, Samarkand turned destruction into ornament. Its blue tiles are the residue of an argument about what a city is for.',
                'categories' => ['cities-ruins', 'empires-conquest'],
                'tags' => ['silk-road', 'architecture', 'trade-routes'],
                'status' => ArticleStatus::Published,
                'days_ago' => 15,
                'views' => 1890,
                'likes' => 121,
                'shares' => 31,
                'content' => <<<'HTML'
<p>Samarkand is one of the oldest continuously inhabited cities in Central Asia, and one of the most repeatedly destroyed. It was a great Silk Road city, then a casualty of conquest, then rebuilt as a capital of empire, then a provincial town, then a monument to itself. Each layer left its mark on the next.</p>

<h2>Blue as an argument</h2>
<p>The city we picture today — the turquoise domes and tiled portals of the Registan — largely dates from the Timurid period, when Timur made Samarkand the capital of an empire stretching from Anatolia to India. Timur reportedly brought craftsmen and scholars from the cities he conquered, and set them to work making his capital the most beautiful in the world.</p>

<h2>Built on what came before</h2>
<p>Beneath the Timurid glory lie older cities: the Achaemenid settlement, the Greek outpost of Maracanda, the Sogdian trading town, and the ruins left by Genghis Khan's armies in 1220. Samarkand did not forget its destructions. It built over them.</p>

<blockquote>Every rebuilding is also a decision about what to remember and what to cover.</blockquote>

<p>In the twentieth century, Soviet restoration and modern tourism turned parts of the old city into a stage set. The result is a place where the question "what is original?" has no simple answer — which may be the most honest monument to a city that was always being remade.</p>
HTML,
            ],
            [
                'title' => 'The Scribes Who Saved the Classics',
                'slug' => 'the-scribes-who-saved-the-classics',
                'author' => 'samuel',
                'image' => 'articles/scriptorium.jpg',
                'alt' => 'A medieval manuscript page with decorated initials',
                'excerpt' => 'The literature of Greece and Rome survived the collapse of the world that made it, not in libraries but in the hands of copyists who often did not understand what they copied.',
                'categories' => ['books-letters', 'time-memory'],
                'tags' => ['manuscripts', 'preservation'],
                'status' => ArticleStatus::Published,
                'days_ago' => 48,
                'views' => 2230,
                'likes' => 164,
                'shares' => 46,
                'content' => <<<'HTML'
<p>Most classical literature does not survive in any ancient copy. It survives because, again and again over more than a thousand years, someone decided to copy it by hand. The scribes who did this were often monks, sometimes working in languages and genres far from their own interests, and they made mistakes. Without them, we would have almost nothing.</p>

<h2>The mechanics of survival</h2>
<p>Papyrus and parchment decay. A text survives only if it is recopied before the physical book fails. That means survival depended on institutions with the resources and reason to keep copying: monasteries, cathedral schools and, crucially, the translation and copying workshops of the Islamic world, where Greek philosophy, medicine and mathematics were preserved and extended.</p>

<h3>Errors as evidence</h3>
<p>Copyists introduce errors, and those errors are useful. Shared mistakes reveal which manuscripts descend from which, letting scholars reconstruct a lost original like a family tree of mistakes. The tree is the method.</p>

<h2>Palimpsests and second chances</h2>
<p>When parchment was scarce, old texts were scraped away and overwritten. Some of these palimpsests can now be read again with ultraviolet light and multi-spectral imaging, recovering works that were assumed lost — an act of modern technology completing the work of ancient scribes.</p>

<p>The classics were not saved by reverence alone. They were saved by labour, repetition and a willingness to copy things that seemed, at the time, merely old.</p>
HTML,
            ],
            [
                'title' => 'Reading Bones in the Desert',
                'slug' => 'reading-bones-in-the-desert',
                'author' => 'marcus',
                'image' => 'articles/gobekli-tepe.jpg',
                'alt' => 'Excavated stone pillars arranged in a circle',
                'excerpt' => 'At Göbekli Tepe, hunter-gatherers raised monumental stone circles before farming, cities or writing. Work in progress on what it means for the history of religion.',
                'categories' => ['ancient-worlds'],
                'tags' => ['archaeology'],
                'status' => ArticleStatus::Draft,
                'content' => <<<'HTML'
<p>This draft explores the ongoing interpretation of Göbekli Tepe and what monumental construction by pre-agricultural communities implies for the standard sequence of human history.</p>

<h2>The site</h2>
<p>Circular enclosures of carved limestone pillars, raised and deliberately buried.</p>

<h2>The argument</h2>
<p>If gathering precedes farming at this scale, the old story of "surplus leads to complexity" needs revision.</p>
HTML,
            ],
            [
                'title' => 'The Woman Who Catalogued a Lost Library',
                'slug' => 'the-woman-who-catalogued-a-lost-library',
                'author' => 'elena',
                'image' => 'articles/hypatia.jpg',
                'alt' => 'Ancient ruins of a classical city by the sea',
                'excerpt' => 'A profile of the scholars, many of them women, whose catalogues and commentaries are the only reason we can imagine the libraries that vanished.',
                'categories' => ['books-letters', 'time-memory'],
                'tags' => ['manuscripts', 'preservation'],
                'status' => ArticleStatus::PendingReview,
                'content' => <<<'HTML'
<p>This piece is awaiting editorial review. It argues that catalogues, indices and marginal notes are a form of preservation in their own right, and profiles several scholars whose work survives only through the way others used it.</p>

<h2>The argument</h2>
<p>Loss is the default; survival is the exception, and it is usually the work of editors rather than authors.</p>
HTML,
            ],
            [
                'title' => 'How Maps Invented the Border',
                'slug' => 'how-maps-invented-the-border',
                'author' => 'tomas',
                'image' => 'articles/history-of-cartography.jpg',
                'alt' => 'An old world map drawn on parchment',
                'excerpt' => 'A line on a map looks like a description of the world. Very often it is an instruction — and a fairly recent one at that.',
                'categories' => ['empires-conquest', 'time-memory'],
                'tags' => ['preservation'],
                'status' => ArticleStatus::Scheduled,
                'days_ahead' => 6,
                'content' => <<<'HTML'
<p>This essay is scheduled for publication. It traces how the modern, surveyed political border emerged from early modern mapmaking and colonial administration, and how a drawn line became a claim that had to be defended.</p>

<h2>From description to instruction</h2>
<p>Earlier maps showed spheres of influence and itineraries rather than hard edges. The surveyed border turned the world into geometry, with consequences that are still being negotiated.</p>

<h2>The cost of precision</h2>
<p>Precision is not neutrality. Every line drawn at a desk was answered, sooner or later, on the ground.</p>
HTML,
            ],
        ];
    }
}
