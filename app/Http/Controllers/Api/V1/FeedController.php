<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;
use App\PostKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeedController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $search = $request->string('q')->trim()->limit(80)->toString();
        $kind = $request->string('kind')->toString();
        $allowedKinds = array_column(PostKind::cases(), 'value');

        $posts = Post::query()
            ->published()
            ->with('user')
            ->withCount(['reactingUsers', 'bookmarkingUsers'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereLike('title', "%{$search}%")
                        ->orWhereLike('body', "%{$search}%")
                        ->orWhereLike('summary', "%{$search}%")
                        ->orWhereLike('source_name', "%{$search}%");
                });
            })
            ->when(in_array($kind, $allowedKinds, true), fn (Builder $query): Builder => $query->where('kind', $kind))
            ->latest('published_at')
            ->latest('id')
            ->cursorPaginate(20);

        return PostResource::collection($posts);
    }
}
