<?php

namespace App\Actions;

use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class StorePostAttachmentsAction
{
    /** @param array<int, UploadedFile> $files */
    public function execute(Post $post, array $files): void
    {
        foreach ($files as $file) {
            $mimeType = $file->getMimeType() ?? 'application/octet-stream';
            $extension = $file->guessExtension() ?: $file->extension();
            $path = $file->storeAs(
                'posts/'.$post->user_id.'/'.$post->getKey(),
                Str::ulid().'.'.$extension,
                ['disk' => 'r2'],
            );

            $post->attachments()->create([
                'disk' => 'r2',
                'path' => $path,
                'media_type' => str_starts_with($mimeType, 'video/') ? 'video' : 'image',
                'mime_type' => $mimeType,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ]);
        }
    }
}
