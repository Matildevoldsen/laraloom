<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PostStatusController as AdminPostStatusController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\ContentRequestController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', FeedController::class)->name('home');
Route::redirect('/dashboard', '/')->name('dashboard');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/@{user:username}', [ProfileController::class, 'show'])->name('profiles.show');

Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/content-policy', [LegalController::class, 'contentPolicy'])->name('legal.content-policy');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/content-request', [ContentRequestController::class, 'create'])->name('legal.content-request');
Route::post('/content-request', [ContentRequestController::class, 'store'])
    ->middleware('throttle:content-requests')
    ->name('legal.content-request.store');

Route::middleware(['auth'])->group(function () {
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])
        ->middleware('throttle:community-publishing')
        ->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::get('/projects/create/new', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])
        ->middleware('throttle:community-publishing')
        ->name('projects.store');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('/@{user:username}/edit', [ProfileController::class, 'edit'])->name('profiles.edit');
    Route::put('/@{user:username}', [ProfileController::class, 'update'])->name('profiles.update');

    Route::post('/posts/{post}/reaction', ReactionController::class)
        ->middleware('throttle:community-interactions')
        ->name('posts.reaction');
    Route::post('/posts/{post}/bookmark', BookmarkController::class)
        ->middleware('throttle:community-interactions')
        ->name('posts.bookmark');
    Route::post('/@{user:username}/follow', FollowController::class)
        ->middleware('throttle:community-interactions')
        ->name('profiles.follow');

    Route::prefix('admin')->name('admin.')->middleware('can:access-admin')->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::patch('/posts/{post}/status', AdminPostStatusController::class)->name('posts.status');
    });
});

require __DIR__.'/settings.php';
