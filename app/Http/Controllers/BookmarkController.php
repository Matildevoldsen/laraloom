<?php

namespace App\Http\Controllers;

use App\Actions\ToggleBookmarkAction;
use App\Models\Post;
use App\Models\User;
use App\PostStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function __invoke(
        Request $request,
        Post $post,
        ToggleBookmarkAction $toggleBookmark,
    ): JsonResponse|RedirectResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        abort_unless($post->status === PostStatus::Published && $post->published_at?->isPast(), 404);
        $active = $toggleBookmark->execute($user, $post);

        if ($request->expectsJson()) {
            return response()->json([
                'active' => $active,
                'count' => $post->bookmarkingUsers()->count(),
            ]);
        }

        return back();
    }
}
