<?php

namespace App\Actions;

use App\Models\User;

class UpdateUserVerificationAction
{
    public function execute(User $administrator, User $member, bool $isVerified): User
    {
        abort_unless($administrator->is_admin === true, 403);

        $member->forceFill(['is_verified' => $isVerified])->save();

        return $member->refresh();
    }
}
