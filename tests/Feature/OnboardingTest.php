<?php

use App\Models\LegalAcceptance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

it('prefills a new GitHub member username before unlocking the community', function (): void {
    Socialite::fake('github', SocialiteUser::fake([
        'id' => 'github-onboarding',
        'nickname' => 'TaylorOtwell',
        'name' => 'Taylor Otwell',
        'email' => 'taylor@example.test',
    ]));

    $this->get(route('auth.github.callback'))
        ->assertRedirect(route('legal.acceptance.show'));

    $user = User::query()->where('github_id', 'github-onboarding')->sole();

    expect($user->username)->toBe('taylorotwell')
        ->and($user->onboarding_completed_at)->toBeNull();

    $this->get(route('legal.acceptance.show'))
        ->assertOk()
        ->assertSee('Choose your username')
        ->assertSee('taylorotwell');

    $this->get(route('posts.create'))
        ->assertRedirect(route('legal.acceptance.show'));
});

it('normalizes and records the chosen username with legal acceptance', function (): void {
    $user = User::factory()
        ->withoutCompletedOnboarding()
        ->withoutLegalAcceptance()
        ->create(['username' => 'provisional-name']);

    $this->actingAs($user)
        ->get(route('posts.create'))
        ->assertRedirect(route('legal.acceptance.show'));

    $this->post(route('legal.acceptance.store'), [
        'username' => '  Confirmed_Name  ',
        'terms_accepted' => '1',
        'age_confirmed' => '1',
    ])->assertRedirect(route('posts.create'));

    expect($user->refresh())
        ->username->toBe('confirmed_name')
        ->onboarding_completed_at->not->toBeNull()
        ->and(LegalAcceptance::query()->whereBelongsTo($user)->count())->toBe(1);
});

it('requires an available username before completing onboarding', function (): void {
    User::factory()->create(['username' => 'already-taken']);
    $user = User::factory()
        ->withoutCompletedOnboarding()
        ->withoutLegalAcceptance()
        ->create(['username' => 'provisional-name']);

    $this->actingAs($user)
        ->post(route('legal.acceptance.store'), [
            'username' => 'already-taken',
            'terms_accepted' => '1',
            'age_confirmed' => '1',
        ])
        ->assertSessionHasErrors('username');

    expect($user->refresh()->onboarding_completed_at)->toBeNull()
        ->and(LegalAcceptance::query()->whereBelongsTo($user)->exists())->toBeFalse();
});

it('returns a precondition response while onboarding is incomplete', function (): void {
    $user = User::factory()->withoutCompletedOnboarding()->create();

    $this->actingAs($user)
        ->getJson(route('direct-messages.index'))
        ->assertStatus(428)
        ->assertJson([
            'message' => 'Complete your profile before using this feature.',
            'acceptance_url' => route('legal.acceptance.show'),
        ]);
});

it('keeps mobile password registration available and marks it complete', function (): void {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Mobile Member',
        'username' => 'mobile_member',
        'email' => 'mobile@example.test',
        'password' => 'secure-password',
        'password_confirmation' => 'secure-password',
        'device_name' => 'iPhone',
    ])->assertCreated();

    expect(User::query()->where('email', 'mobile@example.test')->sole()->onboarding_completed_at)
        ->not->toBeNull();
});
