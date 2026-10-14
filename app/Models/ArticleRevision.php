<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Database\Factories\ArticleRevisionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'article_id',
    'user_id',
    'title',
    'slug',
    'excerpt',
    'content',
    'status',
    'note',
])]
class ArticleRevision extends Model
{
    /** @use HasFactory<ArticleRevisionFactory> */
    use HasFactory;

    protected $touches = ['article'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
        ];
    }

    /** @return BelongsTo<Article, $this> */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
