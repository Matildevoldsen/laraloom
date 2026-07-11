<?php

namespace App\Http\Controllers;

use App\Actions\CreatePostAction;
use App\Actions\UpdatePostAction;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function create(): View
    {
        Gate::authorize('create', Post::class);

        return view('posts.create');
    }

    public function store(StorePostRequest $request, CreatePostAction $createPost): RedirectResponse
    {
        Gate::authorize('create', Post::class);
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $createPost->execute($user, $request->validated());

        return to_route('home')->with('status', 'Your post is live.');
    }

    public function edit(Post $post): View
    {
        Gate::authorize('update', $post);

        return view('posts.edit', compact('post'));
    }

    public function update(
        UpdatePostRequest $request,
        Post $post,
        UpdatePostAction $updatePost,
    ): RedirectResponse {
        $updatePost->execute($post, $request->validated());

        return to_route('home')->with('status', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);
        $post->delete();

        return back()->with('status', 'Post deleted.');
    }
}
