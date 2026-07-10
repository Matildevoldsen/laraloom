<?php

namespace App\Http\Controllers;

use App\Actions\ToggleBookmarkAction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function __invoke(Request $request, Post $post, ToggleBookmarkAction $toggleBookmark): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $toggleBookmark->execute($user, $post);

        return back();
    }
}
