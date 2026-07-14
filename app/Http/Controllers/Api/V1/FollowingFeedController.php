<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FollowingFeedController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $posts = Post::query()
            ->published()
            ->where(function (Builder $query) use ($user): void {
                $query->whereIn('user_id', $user->following()->select('users.id'))
                    ->orWhereHas('repostingUsers', fn (Builder $query): Builder => $query->whereIn(
                        'users.id',
                        $user->following()->select('users.id'),
                    ));
            })
            ->with(['user', 'attachments', 'hashtags', 'mentions.mentionedUser'])
            ->withCount(['reactingUsers', 'bookmarkingUsers', 'repostingUsers', 'comments'])
            ->withExists([
                'reactingUsers as is_reacted' => fn (Builder $query): Builder => $query->whereKey($user->id),
                'bookmarkingUsers as is_bookmarked' => fn (Builder $query): Builder => $query->whereKey($user->id),
                'repostingUsers as is_reposted' => fn (Builder $query): Builder => $query->whereKey($user->id),
            ])
            ->latest('published_at')
            ->latest('id')
            ->cursorPaginate(20);

        return PostResource::collection($posts);
    }
}
