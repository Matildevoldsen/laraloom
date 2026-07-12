<?php

namespace App\Http\Controllers\Admin;

use App\Actions\DeleteUserAsAdministratorAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeleteUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserDeletionController extends Controller
{
    public function __invoke(
        DeleteUserRequest $request,
        User $user,
        DeleteUserAsAdministratorAction $deleteUser,
    ): RedirectResponse {
        $administrator = $request->user();
        abort_unless($administrator instanceof User, 401);

        $memberName = $user->name;
        $deleteUser->execute($administrator, $user);

        return to_route('admin.users.index')
            ->with('status', "{$memberName}'s account was permanently deleted.");
    }
}
