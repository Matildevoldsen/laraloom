<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ModeratePostAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePostStatusRequest;
use App\Models\Post;
use App\PostStatus;
use Illuminate\Http\RedirectResponse;

class PostStatusController extends Controller
{
    public function __invoke(
        UpdatePostStatusRequest $request,
        Post $post,
        ModeratePostAction $moderatePost,
    ): RedirectResponse {
        $status = PostStatus::from($request->string('status')->toString());
        $moderatePost->execute($post, $status);

        return back()->with('status', "Post marked {$status->value}.");
    }
}
