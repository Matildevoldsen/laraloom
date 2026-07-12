<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CompleteOnboardingAction
{
    public function __construct(private AcceptLegalTermsAction $acceptLegalTerms) {}

    public function execute(User $user, string $username): void
    {
        DB::transaction(function () use ($user, $username): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->onboarding_completed_at === null) {
                $lockedUser->forceFill([
                    'username' => $username,
                    'onboarding_completed_at' => now(),
                ])->save();
            }

            $this->acceptLegalTerms->execute($lockedUser);
        }, attempts: 3);
    }
}
