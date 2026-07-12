<?php

namespace App\Actions;

use App\Models\User;
use App\Notifications\WelcomeToSourcefolk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as SocialiteUser;

final class AuthenticateGitHubUserAction
{
    public function execute(SocialiteUser $githubUser): User
    {
        $githubId = Str::of((string) $githubUser->getId())->trim()->toString();
        $email = Str::of((string) $githubUser->getEmail())->trim()->lower()->toString();

        if ($githubId === '') {
            throw ValidationException::withMessages([
                'github' => 'GitHub did not provide an account identifier. Please try again.',
            ]);
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'github' => 'GitHub must provide a verified primary email address to join Sourcefolk.',
            ]);
        }

        $user = DB::transaction(
            fn (): User => $this->resolveUser($githubUser, $githubId, $email),
            attempts: 3,
        );

        if ($user->wasRecentlyCreated) {
            $user->notify(new WelcomeToSourcefolk);
        }

        return $user;
    }

    private function resolveUser(SocialiteUser $githubUser, string $githubId, string $email): User
    {
        $user = User::query()
            ->where('github_id', $githubId)
            ->lockForUpdate()
            ->first();

        if ($user instanceof User) {
            return $this->refreshGitHubIdentity($user, $githubUser, $email);
        }

        $user = User::query()
            ->whereRaw('lower(email) = ?', [$email])
            ->lockForUpdate()
            ->first();

        if ($user instanceof User) {
            if (filled($user->github_id)) {
                throw ValidationException::withMessages([
                    'github' => 'That email address is already connected to another GitHub account.',
                ]);
            }

            $user->forceFill([
                'github_id' => $githubId,
                'github_username' => $this->githubUsername($githubUser),
                'avatar_url' => $user->avatar_url ?: $this->avatarUrl($githubUser),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();

            return $user->refresh();
        }

        $user = new User;
        $user->forceFill([
            'name' => $this->displayName($githubUser),
            'username' => $this->availableUsername($githubUser, $githubId),
            'email' => $email,
            'email_verified_at' => now(),
            'onboarding_completed_at' => null,
            'password' => Str::password(40),
            'github_id' => $githubId,
            'github_username' => $this->githubUsername($githubUser),
            'avatar_url' => $this->avatarUrl($githubUser),
        ])->save();

        return $user;
    }

    private function refreshGitHubIdentity(User $user, SocialiteUser $githubUser, string $email): User
    {
        $attributes = [
            'github_username' => $this->githubUsername($githubUser),
            'avatar_url' => $this->avatarUrl($githubUser) ?? $user->avatar_url,
        ];

        if ($user->email_verified_at === null && Str::lower($user->email) === $email) {
            $attributes['email_verified_at'] = now();
        }

        $user->forceFill($attributes)->save();

        return $user->refresh();
    }

    private function displayName(SocialiteUser $githubUser): string
    {
        $name = Str::of((string) ($githubUser->getName() ?: $githubUser->getNickname()))
            ->squish()
            ->limit(100, '')
            ->toString();

        return $name !== '' ? $name : 'GitHub member';
    }

    private function githubUsername(SocialiteUser $githubUser): ?string
    {
        $username = Str::of((string) $githubUser->getNickname())
            ->trim()
            ->limit(39, '')
            ->toString();

        return $username !== '' ? $username : null;
    }

    private function avatarUrl(SocialiteUser $githubUser): ?string
    {
        $avatarUrl = Str::of((string) $githubUser->getAvatar())->trim()->toString();

        if (! filter_var($avatarUrl, FILTER_VALIDATE_URL) || ! Str::startsWith($avatarUrl, 'https://')) {
            return null;
        }

        return $avatarUrl;
    }

    private function availableUsername(SocialiteUser $githubUser, string $githubId): string
    {
        $base = Str::of((string) $githubUser->getNickname())
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9_-]+/', '-')
            ->trim('-_')
            ->limit(30, '')
            ->toString();

        if (Str::length($base) < 3) {
            $base = 'github-'.Str::substr(hash('sha256', $githubId), 0, 8);
        }

        if (! $this->usernameExists($base)) {
            return $base;
        }

        $hash = Str::substr(hash('sha256', $githubId), 0, 7);
        $attempt = 1;

        do {
            $suffix = '-'.$hash.($attempt > 1 ? '-'.$attempt : '');
            $candidate = Str::limit($base, 30 - Str::length($suffix), '').$suffix;
            $attempt++;
        } while ($this->usernameExists($candidate));

        return $candidate;
    }

    private function usernameExists(string $username): bool
    {
        return User::query()->where('username', $username)->exists();
    }
}
