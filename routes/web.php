<?php

use App\Http\Controllers\Admin\ContentRequestStatusController as AdminContentRequestStatusController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PostStatusController as AdminPostStatusController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\UserDeletionController as AdminUserDeletionController;
use App\Http\Controllers\Admin\UserVerificationController as AdminUserVerificationController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContentRequestController;
use App\Http\Controllers\DirectConversationController;
use App\Http\Controllers\DirectConversationReadController;
use App\Http\Controllers\DirectMessageController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\GitHubAuthenticationController;
use App\Http\Controllers\LegalAcceptanceController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\PostAttachmentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\RepostController;
use App\Http\Controllers\UserAvatarController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', FeedController::class)->name('home');
Route::redirect('/dashboard', '/')->name('dashboard');
Route::get('/register', static fn (): RedirectResponse => to_route('login'))
    ->middleware('guest')
    ->name('register');

Route::middleware(['guest', 'throttle:10,1'])->group(function (): void {
    Route::get('/auth/github/redirect', [GitHubAuthenticationController::class, 'redirect'])
        ->name('auth.github.redirect');
    Route::get('/auth/github/callback', [GitHubAuthenticationController::class, 'callback'])
        ->name('auth.github.callback');
});

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/@{user:username}', [ProfileController::class, 'show'])->name('profiles.show');
Route::get('/posts/{post}', [PostController::class, 'show'])->whereNumber('post')->name('posts.show');
Route::get('/media/{attachment}', PostAttachmentController::class)->name('post-attachments.show');
Route::get('/avatars/{user}', UserAvatarController::class)->name('avatars.show');

Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/content-policy', [LegalController::class, 'contentPolicy'])->name('legal.content-policy');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/content-request', [ContentRequestController::class, 'create'])->name('legal.content-request');
Route::post('/content-request', [ContentRequestController::class, 'store'])
    ->middleware('throttle:content-requests')
    ->name('legal.content-request.store');

Route::middleware('auth')->group(function (): void {
    Route::get('/legal/acceptance', [LegalAcceptanceController::class, 'show'])
        ->name('legal.acceptance.show');
    Route::post('/legal/acceptance', [LegalAcceptanceController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('legal.acceptance.store');
});

Route::middleware(['auth', 'legal.accepted'])->group(function (): void {
    Route::prefix('messages')->name('direct-messages.')->group(function (): void {
        Route::get('/', [DirectConversationController::class, 'index'])->name('index');
        Route::post('/with/{recipient:username}', [DirectConversationController::class, 'store'])
            ->middleware('throttle:direct-messages')
            ->name('store');
        Route::get('/{conversation}', [DirectConversationController::class, 'show'])
            ->whereNumber('conversation')
            ->name('show');
        Route::post('/{conversation}', DirectMessageController::class)
            ->whereNumber('conversation')
            ->middleware('throttle:direct-messages')
            ->name('messages.store');
        Route::put('/{conversation}/read', DirectConversationReadController::class)
            ->whereNumber('conversation')
            ->name('read');
    });

    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])
        ->middleware('throttle:community-publishing')
        ->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:community-interactions')
        ->name('posts.comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/posts/{post}/repost', RepostController::class)
        ->middleware('throttle:community-interactions')
        ->name('posts.repost');

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
        Route::get('/users', AdminUserController::class)->name('users.index');
        Route::patch('/users/{user}/verification', AdminUserVerificationController::class)
            ->name('users.verification');
        Route::delete('/users/{user}', AdminUserDeletionController::class)
            ->name('users.destroy');
        Route::patch('/posts/{post}/status', AdminPostStatusController::class)->name('posts.status');
        Route::patch('/content-requests/{contentRequest}/status', AdminContentRequestStatusController::class)
            ->name('content-requests.status');
    });
});

require __DIR__.'/settings.php';
