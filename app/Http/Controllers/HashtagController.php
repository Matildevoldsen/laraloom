<?php

namespace App\Http\Controllers;

use App\Models\Hashtag;
use App\Models\User;
use App\PostStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class HashtagController extends Controller
{
    public function __invoke(Request $request, Hashtag $hashtag): View
    {
        $viewer = $request->user();
        $hashtag->loadCount([
            'posts as published_posts_count' => fn (Builder $query): Builder => $query
                ->where('status', PostStatus::Published)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now()),
        ]);

        $posts = $hashtag->posts()
            ->published()
            ->with(['user', 'attachments', 'hashtags', 'mentions.mentionedUser'])
            ->withCount(['reactingUsers', 'bookmarkingUsers', 'repostingUsers', 'comments'])
            ->withViewerInteractionState($viewer instanceof User ? $viewer : null)
            ->latest('published_at')
            ->latest('posts.id')
            ->cursorPaginate(12);

        return view('hashtags.show', compact('hashtag', 'posts'));
    }
}
