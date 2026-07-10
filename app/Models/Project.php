<?php

namespace App\Models;

use App\ProjectKind;
use App\ProjectStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property ProjectKind $kind
 * @property ProjectStatus $status
 * @property string $name
 * @property string $slug
 * @property string $tagline
 * @property string $description
 * @property string $url
 * @property string|null $repository_url
 * @property string|null $laravel_cloud_url
 * @property string|null $logo_url
 * @property string|null $screenshot_url
 * @property array<int, string>|null $tags
 * @property bool $is_open_source
 * @property Carbon|null $featured_at
 * @property Carbon|null $published_at
 */
#[Fillable([
    'user_id', 'kind', 'status', 'name', 'slug', 'tagline', 'description',
    'url', 'repository_url', 'laravel_cloud_url', 'logo_url', 'screenshot_url',
    'tags', 'is_open_source', 'featured_at', 'published_at',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'kind' => ProjectKind::class,
            'status' => ProjectStatus::class,
            'tags' => 'array',
            'is_open_source' => 'boolean',
            'featured_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }
}
