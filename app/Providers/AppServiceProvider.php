<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Repost;
use App\Models\User;
use App\Observers\CommunityActivityObserver;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-admin', fn (User $user): bool => $user->is_admin === true);

        Post::observe(CommunityActivityObserver::class);
        Comment::observe(CommunityActivityObserver::class);
        Reaction::observe(CommunityActivityObserver::class);
        Repost::observe(CommunityActivityObserver::class);

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );

        RateLimiter::for('community-publishing', fn (Request $request): Limit => Limit::perHour(12)
            ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip())));

        RateLimiter::for('community-interactions', fn (Request $request): Limit => Limit::perMinute(60)
            ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip())));

        RateLimiter::for('direct-messages', fn (Request $request): array => [
            Limit::perMinute(30)->by('dm-minute:'.($request->user()?->getAuthIdentifier() ?? $request->ip())),
            Limit::perDay(500)->by('dm-day:'.($request->user()?->getAuthIdentifier() ?? $request->ip())),
        ]);

        RateLimiter::for('content-requests', fn (Request $request): Limit => Limit::perDay(5)
            ->by((string) $request->ip()));
    }
}
