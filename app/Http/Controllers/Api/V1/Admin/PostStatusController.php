<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\ModeratePostAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePostStatusRequest;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;
use App\PostStatus;

class PostStatusController extends Controller
{
    public function __invoke(
        UpdatePostStatusRequest $request,
        Post $post,
        ModeratePostAction $moderatePost,
    ): PostResource {
        $status = PostStatus::from($request->string('status')->toString());
        $post = $moderatePost->execute($post, $status);

        return PostResource::make(
            $post->load('user')->loadCount(['reactingUsers', 'bookmarkingUsers', 'repostingUsers', 'comments']),
        );
    }
}
