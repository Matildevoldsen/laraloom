<?php

namespace App\Actions;

use App\Models\User;

class UpdateCommunityProfileAction
{
    /** @param array<string, mixed> $attributes */
    public function execute(User $user, array $attributes): User
    {
        $user->update([
            ...$attributes,
            'username' => strtolower((string) $attributes['username']),
            'stack' => array_values((array) ($attributes['stack'] ?? [])),
            'is_available_for_work' => (bool) ($attributes['is_available_for_work'] ?? false),
        ]);

        return $user->refresh();
    }
}
