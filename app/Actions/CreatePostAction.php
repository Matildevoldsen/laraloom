<?php

namespace App\Actions;

use App\Models\Post;
use App\Models\User;
use App\PostStatus;
use App\Services\PostInputNormalizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class CreatePostAction
{
    public function __construct(
        private readonly PostInputNormalizer $normalizer,
        private readonly SyncPostReferencesAction $syncReferences,
        private readonly StorePostAttachmentsAction $storeAttachments,
        private readonly PersistPostAttachmentsAction $persistAttachments,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function execute(User $user, array $attributes): Post
    {
        $files = $this->attachmentFiles($attributes['attachments'] ?? null);
        unset($attributes['attachments']);
        $input = $this->normalizer->normalize($attributes);
        $title = $input['title'];
        $storedAttachments = $this->storeAttachments->execute($user, $files);

        try {
            return DB::transaction(function () use ($input, $storedAttachments, $title, $user): Post {
                $post = Post::create([
                    'user_id' => $user->id,
                    ...$input,
                    'status' => PostStatus::Published,
                    'slug' => $title ? Str::slug($title).'-'.Str::lower(Str::random(6)) : null,
                    'is_ai_curated' => false,
                    'published_at' => now(),
                ]);

                $this->syncReferences->execute($post);
                $this->persistAttachments->execute($post, $storedAttachments);

                return $post;
            }, attempts: 3);
        } catch (Throwable $exception) {
            $this->storeAttachments->delete($storedAttachments);

            throw $exception;
        }
    }

    /** @return list<UploadedFile> */
    private function attachmentFiles(mixed $attachments): array
    {
        if ($attachments === null) {
            return [];
        }

        if (! is_array($attachments)) {
            throw new InvalidArgumentException('Post attachments must be an array of uploaded files.');
        }

        $files = [];

        foreach ($attachments as $attachment) {
            if (! $attachment instanceof UploadedFile) {
                throw new InvalidArgumentException('Each post attachment must be an uploaded file.');
            }

            $files[] = $attachment;
        }

        return $files;
    }
}
