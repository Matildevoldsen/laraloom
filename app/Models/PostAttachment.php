<?php

namespace App\Models;

use Database\Factories\PostAttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['post_id', 'disk', 'path', 'media_type', 'mime_type', 'original_name', 'size'])]
class PostAttachment extends Model
{
    /** @use HasFactory<PostAttachmentFactory> */
    use HasFactory;

    /** @return BelongsTo<Post, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    protected static function booted(): void
    {
        static::deleted(function (PostAttachment $attachment): void {
            Storage::disk($attachment->disk)->delete($attachment->path);
        });
    }
}
