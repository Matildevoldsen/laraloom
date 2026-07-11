<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentRequest;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\PostStatus;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $counts = [
            'pending_posts' => Post::query()->where('status', PostStatus::Pending)->count(),
            'posts' => Post::query()->count(),
            'projects' => Project::query()->count(),
            'members' => User::query()->count(),
            'content_requests' => ContentRequest::query()->where('status', 'pending')->count(),
        ];
        $posts = Post::query()
            ->with('user')
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->latest()
            ->paginate(30);

        return view('admin.dashboard', compact('counts', 'posts'));
    }
}
