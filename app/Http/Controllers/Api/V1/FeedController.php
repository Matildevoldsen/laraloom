<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;
use App\Models\User;
use App\PostKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeedController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $user = $request->user('sanctum');
        $search = $request->string('q')->trim()->limit(80)->toString();
        $kind = $request->string('kind')->toString();
        $allowedKinds = array_column(PostKind::cases(), 'value');

        $posts = Post::query()
            ->published()
            ->with(['user', 'attachments', 'hashtags', 'mentions.mentionedUser'])
            ->withCount(['reactingUsers', 'bookmarkingUsers', 'repostingUsers', 'comments'])
            ->when($user instanceof User, fn (Builder $query): Builder => $query->withExists([
                'reactingUsers as is_reacted' => fn (Builder $query): Builder => $query->whereKey($user->id),
                'bookmarkingUsers as is_bookmarked' => fn (Builder $query): Builder => $query->whereKey($user->id),
                'repostingUsers as is_reposted' => fn (Builder $query): Builder => $query->whereKey($user->id),
            ]))
            ->when($search !== '', fn (Builder $query): Builder => $query->matchingSearch($search))
            ->when(in_array($kind, $allowedKinds, true), fn (Builder $query): Builder => $query->where('kind', $kind))
            ->latest('published_at')
            ->latest('id')
            ->cursorPaginate(20);

        return PostResource::collection($posts);
    }
}
