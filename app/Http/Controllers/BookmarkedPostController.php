<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BookmarkedPostController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $posts = $user->bookmarkedPosts()
            ->published()
            ->with(['user', 'attachments', 'hashtags', 'mentions.mentionedUser'])
            ->withCount(['reactingUsers', 'bookmarkingUsers', 'repostingUsers', 'comments'])
            ->withViewerInteractionState($user)
            ->orderByPivot('created_at', 'desc')
            ->orderByDesc('posts.id')
            ->cursorPaginate(12);

        return view('bookmarks.index', compact('posts'));
    }
}
