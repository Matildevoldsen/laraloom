<?php

namespace App\Http\Controllers;

use App\Actions\UpdateCommunityProfileAction;
use App\Http\Requests\UpdateCommunityProfileRequest;
use App\Models\User;
use App\PostStatus;
use App\ProjectStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ProfileController extends Controller
{
    public function show(User $user): View
    {
        $viewer = auth()->user();
        $user->loadCount(['followers', 'following', 'posts', 'projects']);
        $user->setAttribute(
            'is_following',
            auth()->check() && auth()->user()->following()->whereKey($user->id)->exists(),
        );
        $user->setAttribute(
            'can_message',
            $viewer instanceof User && $viewer->followers()->whereKey($user->id)->exists(),
        );

        $posts = $user->posts()
            ->where('status', PostStatus::Published)
            ->with('attachments')
            ->withCount(['reactingUsers', 'bookmarkingUsers', 'repostingUsers', 'comments'])
            ->latest('published_at')
            ->limit(12)
            ->get();
        $projects = $user->projects()
            ->where('status', ProjectStatus::Published)
            ->latest('published_at')
            ->get();
        $followers = $user->followers()->limit(12)->get();
        $following = $user->following()->limit(12)->get();
        $replies = $user->comments()->with('post')->latest()->limit(20)->get();
        $likedPosts = $user->reactedPosts()
            ->where('status', PostStatus::Published)
            ->with(['user', 'attachments'])
            ->withCount(['reactingUsers', 'bookmarkingUsers', 'repostingUsers', 'comments'])
            ->latest('reactions.created_at')
            ->limit(20)
            ->get();
        $repostedPosts = $user->repostedPosts()
            ->where('status', PostStatus::Published)
            ->with(['user', 'attachments'])
            ->withCount(['reactingUsers', 'bookmarkingUsers', 'repostingUsers', 'comments'])
            ->latest('reposts.created_at')
            ->limit(20)
            ->get();

        return view('profiles.show', compact(
            'followers',
            'following',
            'likedPosts',
            'posts',
            'projects',
            'replies',
            'repostedPosts',
            'user',
        ));
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return view('profiles.edit', compact('user'));
    }

    public function update(
        UpdateCommunityProfileRequest $request,
        User $user,
        UpdateCommunityProfileAction $updateProfile,
    ): RedirectResponse {
        Gate::authorize('update', $user);
        $user = $updateProfile->execute($user, $request->validated());

        return to_route('profiles.show', $user)->with('status', 'Profile updated.');
    }
}
