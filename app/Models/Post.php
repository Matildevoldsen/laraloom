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
use Illuminate\Support\Str;

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
 * @property bool $is_bookmarked
 * @property bool $is_reacted
 * @property bool $is_reposted
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

    /** @return HasMany<PostAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(PostAttachment::class);
    }

    /** @return BelongsToMany<Hashtag, $this> */
    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(Hashtag::class)->withTimestamps();
    }

    /** @return HasMany<Mention, $this> */
    public function mentions(): HasMany
    {
        return $this->hasMany(Mention::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Post $post): void {
            $post->attachments()->each(fn (PostAttachment $attachment) => $attachment->delete());
        });
    }

    /** @param Builder<Post> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** @param Builder<Post> $query */
    public function scopeMatchingSearch(Builder $query, string $search): void
    {
        $search = Str::of($search)->trim()->limit(80, '')->toString();

        if ($search === '') {
            return;
        }

        if (Str::startsWith($search, '#')) {
            $hashtag = Str::of($search)->after('#')->before(' ')->lower()->toString();

            $query->whereHas('hashtags', fn (Builder $query): Builder => $query->where('slug', $hashtag));

            return;
        }

        $query->where(function (Builder $query) use ($search): void {
            $query->whereLike('title', "%{$search}%")
                ->orWhereLike('body', "%{$search}%")
                ->orWhereLike('summary', "%{$search}%")
                ->orWhereLike('source_name', "%{$search}%")
                ->orWhereHas('hashtags', function (Builder $query) use ($search): void {
                    $query->whereLike('name', "%{$search}%")
                        ->orWhereLike('slug', "%{$search}%");
                })
                ->orWhereHas('user', function (Builder $query) use ($search): void {
                    $query->whereLike('name', "%{$search}%")
                        ->orWhereLike('username', "%{$search}%");
                });
        });
    }

    /** @param Builder<Post> $query */
    public function scopeWithSocialReferences(Builder $query): void
    {
        $query->with(['hashtags', 'mentions.mentionedUser']);
    }

    /** @param Builder<Post> $query */
    public function scopeWithViewerInteractionState(Builder $query, ?User $viewer): void
    {
        if (! $viewer instanceof User) {
            return;
        }

        $query->withExists([
            'reactingUsers as is_reacted' => fn (Builder $query): Builder => $query->whereKey($viewer->id),
            'bookmarkingUsers as is_bookmarked' => fn (Builder $query): Builder => $query->whereKey($viewer->id),
            'repostingUsers as is_reposted' => fn (Builder $query): Builder => $query->whereKey($viewer->id),
        ]);
    }

    public function loadViewerInteractionState(?User $viewer): self
    {
        if (! $viewer instanceof User) {
            return $this;
        }

        $this->loadExists([
            'reactingUsers as is_reacted' => fn (Builder $query): Builder => $query->whereKey($viewer->id),
            'bookmarkingUsers as is_bookmarked' => fn (Builder $query): Builder => $query->whereKey($viewer->id),
            'repostingUsers as is_reposted' => fn (Builder $query): Builder => $query->whereKey($viewer->id),
        ]);

        return $this;
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
