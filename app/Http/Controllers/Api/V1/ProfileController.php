<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProfileResource;
use App\Models\User;
use App\PostStatus;
use App\ProjectStatus;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __invoke(Request $request, User $user): ProfileResource
    {
        abort_if($user->username === null, 404);

        $viewer = $request->user('sanctum');
        $user->setAttribute(
            'is_following',
            $viewer instanceof User && $viewer->following()->whereKey($user->id)->exists(),
        );
        $user->loadCount(['followers', 'following', 'posts', 'projects'])
            ->load([
                'posts' => fn (HasMany $query): HasMany => $query
                    ->where('status', PostStatus::Published)
                    ->with(['user', 'attachments', 'hashtags', 'mentions.mentionedUser'])
                    ->withCount(['reactingUsers', 'bookmarkingUsers', 'repostingUsers', 'comments'])
                    ->latest('published_at')
                    ->limit(20),
                'projects' => fn (HasMany $query): HasMany => $query
                    ->where('status', ProjectStatus::Published)
                    ->with('user')
                    ->latest('published_at')
                    ->limit(20),
                'comments' => fn (HasMany $query): HasMany => $query
                    ->with('user')
                    ->withCount('replies')
                    ->latest()
                    ->limit(20),
                'reactedPosts' => fn (BelongsToMany $query): BelongsToMany => $query
                    ->where('status', PostStatus::Published)
                    ->with(['user', 'attachments', 'hashtags', 'mentions.mentionedUser'])
                    ->withCount(['reactingUsers', 'bookmarkingUsers', 'repostingUsers', 'comments'])
                    ->latest('reactions.created_at')
                    ->limit(20),
                'repostedPosts' => fn (BelongsToMany $query): BelongsToMany => $query
                    ->where('status', PostStatus::Published)
                    ->with(['user', 'attachments', 'hashtags', 'mentions.mentionedUser'])
                    ->withCount(['reactingUsers', 'bookmarkingUsers', 'repostingUsers', 'comments'])
                    ->latest('reposts.created_at')
                    ->limit(20),
                'followers' => fn (BelongsToMany $query): BelongsToMany => $query->limit(12),
                'following' => fn (BelongsToMany $query): BelongsToMany => $query->limit(12),
            ]);

        return ProfileResource::make($user);
    }
}
