<?php

namespace App\Models;

use App\Enums\CommentStatus;
use App\Enums\Permission;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'username',
    'email',
    'password',
    'avatar_path',
    'bio',
    'job_title',
    'role',
    'status',
    'last_login_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar, HasName
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'last_login_at' => 'datetime',
        ];
    }

    /* -----------------------------------------------------------------
     | Relationships
     | ----------------------------------------------------------------- */

    /** @return HasMany<Article, $this> */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    /** @return HasMany<Comment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** @return HasMany<Comment, $this> */
    public function approvedComments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('status', CommentStatus::Approved->value);
    }

    /** @return HasMany<ArticleRevision, $this> */
    public function articleRevisions(): HasMany
    {
        return $this->hasMany(ArticleRevision::class);
    }

    /** @return HasMany<ActivityLog, $this> */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /* -----------------------------------------------------------------
     | Authorization
     | ----------------------------------------------------------------- */

    public function hasPermission(Permission|string $permission): bool
    {
        $permission = $permission instanceof Permission ? $permission : Permission::from($permission);

        return $this->role?->hasPermission($permission) ?? false;
    }

    public function hasAnyPermission(Permission ...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isActive() && $this->hasPermission(Permission::AccessAdminPanel);
    }

    /* -----------------------------------------------------------------
     | Filament contracts
     | ----------------------------------------------------------------- */

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_path
            ? Storage::disk(config('filament.default_filesystem_disk', 'public'))->url($this->avatar_path)
            : null;
    }

    /* -----------------------------------------------------------------
     | Accessors & helpers
     | ----------------------------------------------------------------- */

    public function getFirstNameAttribute(): string
    {
        return str($this->name)->before(' ')->toString();
    }

    public function getInitialsAttribute(): string
    {
        return str($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn (string $part): string => str($part)->substr(0, 1)->upper()->toString())
            ->implode('');
    }

    /* -----------------------------------------------------------------
     | Scopes
     | ----------------------------------------------------------------- */

    /** @param Builder<User> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', UserStatus::Active->value);
    }

    /** @param Builder<User> $query */
    public function scopeWithRole(Builder $query, UserRole $role): void
    {
        $query->where('role', $role->value);
    }
}
