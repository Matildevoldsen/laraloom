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
        $user->loadCount(['followers', 'following', 'posts', 'projects']);

        $posts = $user->posts()
            ->where('status', PostStatus::Published)
            ->withCount(['reactingUsers', 'bookmarkingUsers', 'repostingUsers', 'comments'])
            ->latest('published_at')
            ->limit(12)
            ->get();
        $projects = $user->projects()
            ->where('status', ProjectStatus::Published)
            ->latest('published_at')
            ->get();

        return view('profiles.show', compact('posts', 'projects', 'user'));
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
