<?php

namespace App\Http\Controllers\Admin;

use App\Actions\UpdateUserVerificationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserVerificationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserVerificationController extends Controller
{
    public function __invoke(
        UpdateUserVerificationRequest $request,
        User $user,
        UpdateUserVerificationAction $updateVerification,
    ): RedirectResponse {
        $administrator = $request->user();
        abort_unless($administrator instanceof User, 401);

        $user = $updateVerification->execute(
            $administrator,
            $user,
            $request->boolean('is_verified'),
        );

        $status = $user->is_verified ? 'verified' : 'unverified';

        return back()->with('status', "{$user->name} is now {$status}.");
    }
}
