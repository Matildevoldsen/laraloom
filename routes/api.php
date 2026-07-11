<?php

use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\PostStatusController as AdminPostStatusController;
use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\BookmarkController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\DeleteAccountController;
use App\Http\Controllers\Api\V1\FeedController;
use App\Http\Controllers\Api\V1\FollowController;
use App\Http\Controllers\Api\V1\FollowingFeedController;
use App\Http\Controllers\Api\V1\MyPostController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ReactionController;
use App\Http\Controllers\Api\V1\RegistrationController;
use App\Http\Controllers\Api\V1\RepostController;
use Illuminate\Broadcasting\BroadcastController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/feed', FeedController::class)->name('feed');
    Route::apiResource('posts', PostController::class)->only('show');
    Route::get('/posts/{post}/comments', [CommentController::class, 'index'])->name('posts.comments.index');
    Route::apiResource('projects', ProjectController::class)->only(['index', 'show']);
    Route::get('/profiles/{user:username}', ProfileController::class)->name('profiles.show');
    Route::post('/auth/token', [AuthTokenController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('auth.store');
    Route::post('/auth/register', RegistrationController::class)
        ->middleware('throttle:5,1')
        ->name('auth.register');

    Route::middleware(['auth:sanctum', 'abilities:mobile'])->group(function (): void {
        Route::post('/broadcasting/auth', [BroadcastController::class, 'authenticate'])
            ->name('broadcasting.auth');
        Route::get('/me', [AuthTokenController::class, 'show'])->name('me');
        Route::get('/me/posts', MyPostController::class)->name('me.posts');
        Route::delete('/auth/token', [AuthTokenController::class, 'destroy'])->name('auth.destroy');
        Route::delete('/me', DeleteAccountController::class)->name('me.destroy');
        Route::get('/feed/following', FollowingFeedController::class)->name('feed.following');
        Route::post('/posts', [PostController::class, 'store'])
            ->middleware('throttle:community-publishing')
            ->name('posts.store');
        Route::patch('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
        Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
            ->middleware('throttle:community-interactions')
            ->name('posts.comments.store');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
        Route::post('/posts/{post}/repost', RepostController::class)
            ->middleware('throttle:community-interactions')
            ->name('posts.repost');
        Route::post('/posts/{post}/reaction', ReactionController::class)
            ->middleware('throttle:community-interactions')
            ->name('posts.reaction');
        Route::post('/posts/{post}/bookmark', BookmarkController::class)
            ->middleware('throttle:community-interactions')
            ->name('posts.bookmark');
        Route::post('/profiles/{user:username}/follow', FollowController::class)
            ->middleware('throttle:community-interactions')
            ->name('profiles.follow');

        Route::prefix('admin')->name('admin.')->middleware('can:access-admin')->group(function (): void {
            Route::get('/', AdminDashboardController::class)->name('dashboard');
            Route::patch('/posts/{post}/status', AdminPostStatusController::class)->name('posts.status');
        });
    });
});
