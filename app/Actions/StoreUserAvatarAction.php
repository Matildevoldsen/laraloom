<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StoreUserAvatarAction
{
    /** @return array{avatar_disk: string, avatar_path: string} */
    public function store(User $user, UploadedFile $avatar): array
    {
        $disk = 'r2';
        $extension = $avatar->extension() ?: 'jpg';
        $path = 'avatars/'.$user->getKey().'/'.Str::uuid().'.'.$extension;
        $storedPath = Storage::disk($disk)->putFileAs(
            dirname($path),
            $avatar,
            basename($path),
        );

        if ($storedPath === false) {
            throw new RuntimeException('The profile photo could not be stored.');
        }

        return [
            'avatar_disk' => $disk,
            'avatar_path' => $storedPath,
        ];
    }

    /** @param array{avatar_disk: string|null, avatar_path: string|null} $avatar */
    public function delete(array $avatar): void
    {
        if (blank($avatar['avatar_disk']) || blank($avatar['avatar_path'])) {
            return;
        }

        Storage::disk($avatar['avatar_disk'])->delete($avatar['avatar_path']);
    }
}
