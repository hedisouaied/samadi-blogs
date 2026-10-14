# Content workflow

## Article lifecycle

Articles move through a five-state lifecycle defined by `App\Enums\ArticleStatus`:

| Status | Value | Meaning |
| --- | --- | --- |
| Draft | `draft` | Work in progress, not visible. |
| Pending Review | `pending_review` | Submitted by an author, awaiting an editor. |
| Scheduled | `scheduled` | Approved and queued for automatic publication. |
| Published | `published` | Live (when active and `published_at` is in the past). |
| Archived | `archived` | Retired, retained for reference. |

Transitions are performed by `ArticleService` and surfaced as table actions:

| Action | From | Result |
| --- | --- | --- |
| Submit for review | Draft / Pending Review | `pending_review` |
| Publish | any | `published`, `published_at` set if empty, `scheduled_at` cleared |
| Schedule | any | `scheduled`, `scheduled_at` set, `published_at` cleared |
| Unpublish | any | `draft` |
| Archive | any | `archived` |
| Save as draft | any | `draft` |

Every transition runs inside a database transaction, captures an article revision
snapshot and writes an activity log entry.

### Scheduled publishing

`Article::scopeDueForPublication()` selects `scheduled` articles whose `scheduled_at`
is in the past and `is_active` is true. The
`php artisan articles:publish-scheduled` command publishes them and is registered in
`routes/console.php` to run every minute. The operation is idempotent.

### Revisions

`ArticleService::captureRevision()` stores a snapshot of the editorial fields
(`title`, `slug`, `excerpt`, `content`, `status`). Revisions are listed in a relation
manager on the article and can be restored from the `ArticleRevisionResource`
(guarded by `Permission::UpdateAnyArticle`). A revision is captured on every state
transition, on save when watched fields change, and once on creation.

### Reading time

`Article::calculateReadingTime()` estimates minutes from the word count of the
stripped content, using the configurable `content.reading_time_wpm` setting
(default 200). It is recalculated on save and on publish.

### SEO & visibility

An article is considered publicly visible when it is `published`, `is_active` and
`published_at` is in the past. SEO fields (`seo_title`, `seo_description`,
`canonical_url`, `seo_noindex`) override the site-wide defaults.

## Comment moderation

Comments use `App\Enums\CommentStatus`:

| Status | Value |
| --- | --- |
| Pending | `pending` |
| Approved | `approved` |
| Rejected | `rejected` |
| Spam | `spam` |
| Trash | `trash` |

Rules enforced by the application:

1. **Depth limit.** Threads are capped at `Comment::MAX_DEPTH` (3). Attempting to reply
   deeper attaches the reply to the nearest ancestor at the maximum depth.
2. **Parent-before-child approval.** A comment may only be approved when its parent is
   approved (`Comment::canBeApproved()`). The edit page normalises an invalid approval
   back to pending.
3. **Reply-preserving deletion.** Deleting a comment routes through
   `CommentService::deletePreservingReplies()`, which re-parents the deleted comment's
   replies to the deleted comment's parent (or promotes them to roots) rather than
   cascading the deletion.
4. **Moderation metadata.** Approving records `approved_at` and `approved_by`.

The comments list defaults to the Pending filter, shows a navigation badge with the
pending count, and offers per-row and bulk moderation actions.
