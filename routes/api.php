<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\BookmarkController;
use App\Http\Controllers\Api\V1\FeedController;
use App\Http\Controllers\Api\V1\FollowController;
use App\Http\Controllers\Api\V1\FollowingFeedController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ReactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/feed', FeedController::class)->name('feed');
    Route::apiResource('posts', PostController::class)->only('show');
    Route::apiResource('projects', ProjectController::class)->only(['index', 'show']);
    Route::get('/profiles/{user:username}', ProfileController::class)->name('profiles.show');
    Route::post('/auth/token', [AuthTokenController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('auth.store');

    Route::middleware(['auth:sanctum', 'abilities:mobile'])->group(function (): void {
        Route::get('/me', [AuthTokenController::class, 'show'])->name('me');
        Route::delete('/auth/token', [AuthTokenController::class, 'destroy'])->name('auth.destroy');
        Route::get('/feed/following', FollowingFeedController::class)->name('feed.following');
        Route::post('/posts/{post}/reaction', ReactionController::class)
            ->middleware('throttle:community-interactions')
            ->name('posts.reaction');
        Route::post('/posts/{post}/bookmark', BookmarkController::class)
            ->middleware('throttle:community-interactions')
            ->name('posts.bookmark');
        Route::post('/profiles/{user:username}/follow', FollowController::class)
            ->middleware('throttle:community-interactions')
            ->name('profiles.follow');
    });
});
