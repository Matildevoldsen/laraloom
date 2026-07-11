<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\ToggleFollowAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __invoke(Request $request, User $user, ToggleFollowAction $toggleFollow): JsonResponse
    {
        $follower = $request->user();
        abort_unless($follower instanceof User, 401);
        abort_if($follower->is($user), 422, 'You cannot follow yourself.');

        $isFollowing = $toggleFollow->execute($follower, $user);

        return response()->json([
            'active' => $isFollowing,
            'count' => $user->followers()->count(),
        ]);
    }
}
