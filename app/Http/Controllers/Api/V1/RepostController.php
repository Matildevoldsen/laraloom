<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\ToggleRepostAction;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RepostController extends Controller
{
    public function __invoke(Request $request, Post $post, ToggleRepostAction $toggleRepost): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        abort_unless($post->published_at?->isPast(), 404);

        $isReposted = $toggleRepost->execute($user, $post);

        return response()->json([
            'active' => $isReposted,
            'count' => $post->repostingUsers()->count(),
        ]);
    }
}
