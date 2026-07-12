<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteUserAsAdministratorAction
{
    public function __construct(private DeleteUserAction $deleteUser) {}

    /**
     * @throws ValidationException
     */
    public function execute(User $administrator, User $member): void
    {
        abort_unless($administrator->is_admin === true, 403);

        DB::transaction(function () use ($administrator, $member): void {
            $lockedMember = User::query()
                ->whereKey($member->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedMember->is_admin === true) {
                $administratorIds = User::query()
                    ->where('is_admin', true)
                    ->lockForUpdate()
                    ->pluck('id');

                if ($administratorIds->count() <= 1) {
                    throw ValidationException::withMessages([
                        'user' => 'The last remaining administrator cannot be deleted.',
                    ]);
                }
            }

            if ($administrator->is($lockedMember)) {
                throw ValidationException::withMessages([
                    'user' => 'You cannot delete your own administrator account.',
                ]);
            }

            $this->deleteUser->execute($lockedMember);
        });
    }
}
