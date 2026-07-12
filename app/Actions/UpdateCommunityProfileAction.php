<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class UpdateCommunityProfileAction
{
    public function __construct(private StoreUserAvatarAction $storeUserAvatar) {}

    /** @param array<string, mixed> $attributes */
    public function execute(User $user, array $attributes): User
    {
        $avatar = $attributes['avatar'] ?? null;
        unset($attributes['avatar']);

        $storedAvatar = null;
        $previousAvatar = null;

        try {
            $updatedUser = DB::transaction(function () use (
                $avatar,
                $attributes,
                &$previousAvatar,
                &$storedAvatar,
                $user,
            ): User {
                $lockedUser = User::query()
                    ->whereKey($user->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $username = strtolower((string) $attributes['username']);
                $usernameChanged = $username !== strtolower((string) $lockedUser->username);

                $this->ensureUsernameCanBeChanged($lockedUser, $usernameChanged);

                if ($avatar instanceof UploadedFile) {
                    $previousAvatar = [
                        'avatar_disk' => $lockedUser->avatar_disk,
                        'avatar_path' => $lockedUser->avatar_path,
                    ];
                    $storedAvatar = $this->storeUserAvatar->store($lockedUser, $avatar);
                }

                $lockedUser->update([
                    ...$attributes,
                    ...($storedAvatar ?? []),
                    'username' => $username,
                    'username_changed_at' => $usernameChanged ? now() : $lockedUser->username_changed_at,
                    'stack' => array_values((array) ($attributes['stack'] ?? [])),
                    'is_available_for_work' => (bool) ($attributes['is_available_for_work'] ?? false),
                ]);

                return $lockedUser;
            });
        } catch (Throwable $exception) {
            if (is_array($storedAvatar)) {
                $this->storeUserAvatar->delete($storedAvatar);
            }

            throw $exception;
        }

        if (is_array($previousAvatar) && is_array($storedAvatar)) {
            $this->storeUserAvatar->delete($previousAvatar);
        }

        return $updatedUser->refresh();
    }

    private function ensureUsernameCanBeChanged(User $user, bool $usernameChanged): void
    {
        if (! $usernameChanged || $user->username_changed_at === null) {
            return;
        }

        $availableAt = $user->username_changed_at->copy()->addMonthNoOverflow();

        if ($availableAt->isFuture()) {
            throw ValidationException::withMessages([
                'username' => 'You can change your username again on '.$availableAt->format('j F Y').'.',
            ]);
        }
    }
}
