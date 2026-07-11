<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\ContentRequest;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\PostStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $posts = Post::query()
            ->with('user')
            ->withCount(['reactingUsers', 'bookmarkingUsers'])
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->latest()
            ->limit(40)
            ->get();

        return response()->json([
            'data' => [
                'counts' => [
                    'pending_posts' => Post::query()->where('status', PostStatus::Pending)->count(),
                    'posts' => Post::query()->count(),
                    'projects' => Project::query()->count(),
                    'members' => User::query()->count(),
                    'content_requests' => ContentRequest::query()->where('status', 'pending')->count(),
                ],
                'posts' => PostResource::collection($posts)->resolve($request),
            ],
        ]);
    }
}
