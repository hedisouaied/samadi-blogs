<?php

namespace App\Services;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Coordinates the editorial lifecycle of an article: state transitions,
 * revision snapshots, duplication and scheduled publishing.
 *
 * State transitions are wrapped in database transactions because they may
 * touch several related records (article, revision, activity log).
 */
class ArticleService
{
    public function __construct(
        protected ActivityLogger $activity,
        protected SettingsService $settings,
    ) {}

    public function publish(Article $article, ?User $actor = null, ?string $note = null): Article
    {
        return DB::transaction(function () use ($article, $actor, $note): Article {
            $article->status = ArticleStatus::Published;
            $article->is_active = true;
            $article->scheduled_at = null;
            $article->published_at ??= now();
            $article->syncReadingTime($this->settings->int('content.reading_time_wpm', 200));
            $article->save();

            $this->captureRevision($article, $actor, $note ?? 'Published');
            $this->activity->log('article.published', $article, "Published article \"{$article->title}\"", [], $actor?->getKey());

            return $article;
        });
    }

    public function schedule(Article $article, CarbonInterface $when, ?User $actor = null): Article
    {
        return DB::transaction(function () use ($article, $when, $actor): Article {
            $article->status = ArticleStatus::Scheduled;
            $article->is_active = true;
            $article->published_at = null;
            $article->scheduled_at = $when;
            $article->save();

            $this->captureRevision($article, $actor, 'Scheduled for '.$when->toDateTimeString());
            $this->activity->log('article.scheduled', $article, "Scheduled article \"{$article->title}\"", [
                'scheduled_at' => $when->toDateTimeString(),
            ], $actor?->getKey());

            return $article;
        });
    }

    public function unpublish(Article $article, ?User $actor = null): Article
    {
        return DB::transaction(function () use ($article, $actor): Article {
            $article->status = ArticleStatus::Draft;
            $article->published_at = null;
            $article->scheduled_at = null;
            $article->save();

            $this->captureRevision($article, $actor, 'Unpublished');
            $this->activity->log('article.unpublished', $article, "Unpublished article \"{$article->title}\"", [], $actor?->getKey());

            return $article;
        });
    }

    public function archive(Article $article, ?User $actor = null): Article
    {
        return DB::transaction(function () use ($article, $actor): Article {
            $article->status = ArticleStatus::Archived;
            $article->save();

            $this->captureRevision($article, $actor, 'Archived');
            $this->activity->log('article.archived', $article, "Archived article \"{$article->title}\"", [], $actor?->getKey());

            return $article;
        });
    }

    public function submitForReview(Article $article, ?User $actor = null): Article
    {
        return DB::transaction(function () use ($article, $actor): Article {
            if (in_array($article->status, [ArticleStatus::Draft, ArticleStatus::PendingReview], true)) {
                $article->status = ArticleStatus::PendingReview;
                $article->save();
            }

            $this->activity->log('article.submitted', $article, "Submitted article \"{$article->title}\" for review", [], $actor?->getKey());

            return $article;
        });
    }

    public function saveAsDraft(Article $article, ?User $actor = null): Article
    {
        $article->status = ArticleStatus::Draft;
        $article->save();

        $this->activity->log('article.drafted', $article, "Moved article \"{$article->title}\" to draft", [], $actor?->getKey());

        return $article;
    }

    public function restore(Article $article, ?User $actor = null): Article
    {
        $article->restore();

        $this->activity->log('article.restored', $article, "Restored article \"{$article->title}\"", [], $actor?->getKey());

        return $article;
    }

    public function toggleFeatured(Article $article, ?User $actor = null): Article
    {
        $article->is_featured = ! $article->is_featured;
        $article->save();

        $this->activity->log(
            $article->is_featured ? 'article.featured' : 'article.unfeatured',
            $article,
            ($article->is_featured ? 'Marked' : 'Unmarked')." \"{$article->title}\" as featured",
            [],
            $actor?->getKey(),
        );

        return $article;
    }

    /**
     * Duplicate an article as a fresh draft while preserving taxonomy.
     */
    public function duplicate(Article $article, ?User $actor = null): Article
    {
        return DB::transaction(function () use ($article, $actor): Article {
            $copy = $article->replicate([
                'slug',
                'view_count',
                'like_count',
                'share_count',
                'published_at',
                'scheduled_at',
            ]);

            $copy->title = $article->title.' (Copy)';
            $copy->slug = null;
            $copy->status = ArticleStatus::Draft;
            $copy->is_featured = false;
            $copy->is_active = true;
            $copy->published_at = null;
            $copy->scheduled_at = null;
            $copy->author_id = $actor?->getKey() ?? $article->author_id;
            $copy->save();

            $copy->categories()->sync($article->categories()->pluck('categories.id')->all());
            $copy->tags()->sync($article->tags()->pluck('tags.id')->all());

            $this->activity->log('article.duplicated', $copy, "Duplicated article \"{$article->title}\"", [
                'source_id' => $article->getKey(),
            ], $actor?->getKey());

            return $copy;
        });
    }

    public function toggleActive(Article $article, ?User $actor = null): Article
    {
        $article->is_active = ! $article->is_active;
        $article->save();

        $this->activity->log(
            $article->is_active ? 'article.activated' : 'article.deactivated',
            $article,
            ($article->is_active ? 'Activated' : 'Deactivated')." article \"{$article->title}\"",
            [],
            $actor?->getKey(),
        );

        return $article;
    }

    /**
     * Publish every scheduled article whose time has arrived. Safe to run
     * repeatedly — only eligible articles are transitioned.
     */
    public function publishDueScheduled(?User $actor = null): int
    {
        $published = 0;

        Article::query()
            ->dueForPublication()
            ->orderBy('scheduled_at')
            ->chunkById(50, function ($articles) use (&$published, $actor): void {
                foreach ($articles as $article) {
                    $this->publish($article, $actor, 'Auto-published by scheduler');
                    $published++;
                }
            });

        return $published;
    }

    /**
     * Persist a revision snapshot of the current article state.
     */
    public function captureRevision(Article $article, ?User $actor = null, ?string $note = null): ArticleRevision
    {
        return ArticleRevision::query()->create([
            'article_id' => $article->getKey(),
            'user_id' => $actor?->getKey(),
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'status' => $article->status,
            'note' => $note,
        ]);
    }

    /**
     * Capture a revision only when meaningful editorial fields changed.
     *
     * @param  array<string, mixed>  $original
     */
    public function maybeCaptureRevision(Article $article, array $original, ?User $actor = null): ?ArticleRevision
    {
        $watched = ['title', 'slug', 'excerpt', 'content', 'status'];

        $changed = collect($watched)->contains(
            fn (string $attribute): bool => ($original[$attribute] ?? null) != $article->getAttribute($attribute)
        );

        if (! $changed) {
            return null;
        }

        return $this->captureRevision($article, $actor, 'Content updated');
    }

    /**
     * Seed an initial revision for a newly created article.
     */
    public function captureInitialRevision(Article $article, ?User $actor = null): ArticleRevision
    {
        return $this->captureRevision($article, $actor, 'Initial version');
    }
}
