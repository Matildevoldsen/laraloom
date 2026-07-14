<?php

namespace App\Models;

use Database\Factories\HashtagFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $published_posts_count
 */
#[Fillable(['name', 'slug'])]
class Hashtag extends Model
{
    /** @use HasFactory<HashtagFactory> */
    use HasFactory;

    /** @return BelongsToMany<Post, $this> */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
