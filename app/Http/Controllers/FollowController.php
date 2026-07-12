<?php

namespace App\Http\Controllers;

use App\Actions\ToggleFollowAction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __invoke(Request $request, User $user, ToggleFollowAction $toggleFollow): RedirectResponse
    {
        $follower = $request->user();
        abort_unless($follower instanceof User, 401);
        abort_if($follower->is($user), 422, 'You cannot follow yourself.');
        $isFollowing = $toggleFollow->execute($follower, $user);

        return back()->with('status', $isFollowing
            ? "You are now following {$user->name}."
            : "You unfollowed {$user->name}.");
    }
}
