<?php

namespace App\Models;

use App\PostKind;
use App\PostStatus;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property PostKind $kind
 * @property PostStatus $status
 * @property string|null $title
 * @property string|null $slug
 * @property string|null $body
 * @property string|null $summary
 * @property string|null $why_it_matters
 * @property string|null $url
 * @property string|null $canonical_url_hash
 * @property string|null $source_name
 * @property string|null $source_author
 * @property array<int, string>|null $tags
 * @property array<string, mixed>|null $metadata
 * @property bool $is_ai_curated
 * @property int|null $ai_confidence
 * @property Carbon|null $source_published_at
 * @property Carbon|null $published_at
 */
#[Fillable([
    'user_id', 'kind', 'status', 'title', 'slug', 'body', 'summary',
    'why_it_matters', 'url', 'canonical_url_hash', 'source_name',
    'source_author', 'source_published_at', 'tags', 'metadata',
    'is_ai_curated', 'ai_confidence', 'published_at',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function reactingUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'reactions')->withTimestamps();
    }

    /** @return BelongsToMany<User, $this> */
    public function bookmarkingUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookmarks')->withTimestamps();
    }

    /** @return BelongsToMany<User, $this> */
    public function repostingUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'reposts')->withTimestamps();
    }

    /** @return HasMany<Comment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** @param Builder<Post> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'kind' => PostKind::class,
            'status' => PostStatus::class,
            'tags' => 'array',
            'metadata' => 'array',
            'is_ai_curated' => 'boolean',
            'ai_confidence' => 'integer',
            'source_published_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }
}
