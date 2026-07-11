<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\PostKind;
use App\ProjectStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function __invoke(Request $request): View
    {
        $feed = $request->string('feed')->toString() ?: 'today';
        $search = $request->string('q')->trim()->limit(80)->toString();

        $posts = Post::query()
            ->published()
            ->with('user')
            ->withCount(['reactingUsers', 'bookmarkingUsers', 'repostingUsers', 'comments'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereLike('title', "%{$search}%")
                        ->orWhereLike('body', "%{$search}%")
                        ->orWhereLike('summary', "%{$search}%")
                        ->orWhereLike('source_name', "%{$search}%");
                });
            })
            ->when($feed === 'following' && $request->user() instanceof User, function (Builder $query) use ($request): void {
                $query->whereIn('user_id', $request->user()->following()->pluck('users.id'));
            })
            ->when($feed === 'packages', fn (Builder $query): Builder => $query->where('kind', PostKind::Package))
            ->when($feed === 'cloud', fn (Builder $query): Builder => $query->where('kind', PostKind::Project))
            ->latest('published_at')
            ->cursorPaginate(12)
            ->withQueryString();

        $projects = Project::query()
            ->where('status', ProjectStatus::Published)
            ->with('user')
            ->latest('featured_at')
            ->latest('published_at')
            ->limit(5)
            ->get();

        $people = User::query()
            ->whereNotNull('username')
            ->withCount(['followers', 'projects'])
            ->orderByDesc('followers_count')
            ->orderByDesc('projects_count')
            ->limit(5)
            ->get();

        return view('feed.index', compact('feed', 'people', 'posts', 'projects', 'search'));
    }
}
