<?php

namespace App\Actions;

use App\Data\StoredPostAttachment;
use App\Data\StoredPostAttachments;
use App\Models\User;
use App\PostAttachmentMediaType;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class StorePostAttachmentsAction
{
    private const string DISK = 'r2';

    public function __construct(private readonly FilesystemFactory $filesystems) {}

    /**
     * @param  list<UploadedFile>  $files
     */
    public function execute(User $user, array $files): StoredPostAttachments
    {
        if ($files === []) {
            return new StoredPostAttachments([]);
        }

        $disk = $this->filesystems->disk(self::DISK);
        $directory = 'posts/'.$user->getKey().'/'.Str::ulid();
        $storedAttachments = [];
        $pathsToClean = [];

        try {
            foreach ($files as $file) {
                $metadata = $this->metadata($file);
                $filename = Str::ulid().'.'.$metadata['extension'];
                $intendedPath = $directory.'/'.$filename;
                $pathsToClean[] = $intendedPath;
                $storedPath = $disk->putFileAs($directory, $file, $filename, []);

                if ($storedPath === false) {
                    throw new RuntimeException('A post attachment could not be stored.');
                }

                if ($storedPath !== $intendedPath) {
                    $pathsToClean[] = $storedPath;
                }
                $storedAttachments[] = new StoredPostAttachment(
                    disk: self::DISK,
                    path: $storedPath,
                    mediaType: $metadata['media_type'],
                    mimeType: $metadata['mime_type'],
                    originalName: $file->getClientOriginalName(),
                    size: $metadata['size'],
                );
            }
        } catch (Throwable $exception) {
            $this->deletePaths($disk, $pathsToClean);

            throw $exception;
        }

        return new StoredPostAttachments($storedAttachments);
    }

    public function delete(StoredPostAttachments $attachments): void
    {
        foreach ($attachments->pathsByDisk() as $disk => $paths) {
            $this->deletePaths($this->filesystems->disk($disk), $paths);
        }
    }

    /**
     * @return array{
     *     extension: string,
     *     media_type: PostAttachmentMediaType,
     *     mime_type: string,
     *     size: int
     * }
     */
    private function metadata(UploadedFile $file): array
    {
        $extension = $file->guessExtension() ?: $file->extension();
        $size = $file->getSize();

        if ($extension === '' || $size === false) {
            throw new RuntimeException('A post attachment could not be read.');
        }

        $mimeType = $file->getMimeType() ?? 'application/octet-stream';

        return [
            'extension' => $extension,
            'media_type' => str_starts_with($mimeType, 'video/')
                ? PostAttachmentMediaType::Video
                : PostAttachmentMediaType::Image,
            'mime_type' => $mimeType,
            'size' => $size,
        ];
    }

    /** @param list<string> $paths */
    private function deletePaths(Filesystem $disk, array $paths): void
    {
        if ($paths === []) {
            return;
        }

        if (! $disk->delete($paths)) {
            throw new RuntimeException('Stored post attachments could not be removed.');
        }
    }
}
