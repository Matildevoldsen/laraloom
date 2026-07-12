<?php

use App\Models\User;
use App\Notifications\WelcomeToSourcefolk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

test('a new GitHub member receives one queued welcome email', function (): void {
    Notification::fake();
    Socialite::fake('github', SocialiteUser::fake([
        'id' => 'welcome-github-member',
        'nickname' => 'newmaker',
        'name' => 'New Maker',
        'email' => 'new@example.test',
    ]));

    $this->get(route('auth.github.callback'))->assertRedirect(route('legal.acceptance.show'));

    $user = User::query()->where('github_id', 'welcome-github-member')->sole();

    Notification::assertSentTo($user, WelcomeToSourcefolk::class, 1);

    auth()->logout();
    Socialite::fake('github', SocialiteUser::fake([
        'id' => 'welcome-github-member',
        'nickname' => 'newmaker',
        'name' => 'New Maker',
        'email' => 'new@example.test',
    ]));

    $this->get(route('auth.github.callback'));

    Notification::assertSentTo($user, WelcomeToSourcefolk::class, 1);
});

test('mobile registration receives a welcome email', function (): void {
    Notification::fake();

    $this->postJson(route('api.v1.auth.register'), [
        'name' => 'Native Member',
        'username' => 'native-member',
        'email' => 'native@example.test',
        'password' => 'secure-password',
        'password_confirmation' => 'secure-password',
        'device_name' => 'iPhone',
    ])->assertCreated();

    Notification::assertSentTo(
        User::query()->where('email', 'native@example.test')->sole(),
        WelcomeToSourcefolk::class,
        1,
    );
});
