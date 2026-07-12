<?php

use App\Actions\UpdateUserVerificationAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

test('only administrators can open member verification', function () {
    $member = User::factory()->create();
    $verifiedMember = User::factory()->verified()->create();

    $this->actingAs($member)
        ->get(route('admin.users.index'))
        ->assertForbidden();

    $this->actingAs($verifiedMember)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('an administrator can verify and unverify a member', function () {
    $administrator = User::factory()->create(['is_admin' => true]);
    $member = User::factory()->create(['name' => 'Community Builder']);

    $this->actingAs($administrator)
        ->patch(route('admin.users.verification', $member), ['is_verified' => true])
        ->assertRedirect()
        ->assertSessionHas('status', 'Community Builder is now verified.');

    expect($member->refresh()->is_verified)->toBeTrue();

    $this->actingAs($administrator)
        ->patch(route('admin.users.verification', $member), ['is_verified' => false])
        ->assertRedirect();

    expect($member->refresh()->is_verified)->toBeFalse();
});

test('a member cannot toggle verification on web or mobile', function () {
    $member = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($member)
        ->patch(route('admin.users.verification', $target), ['is_verified' => true])
        ->assertForbidden();

    Sanctum::actingAs($member, ['mobile']);
    $this->patchJson(route('api.v1.admin.users.verification', $target), ['is_verified' => true])
        ->assertForbidden();

    expect($target->refresh()->is_verified)->toBeFalse();
});

test('the verification action independently rejects non administrators', function () {
    $member = User::factory()->create();
    $target = User::factory()->create();

    app(UpdateUserVerificationAction::class)->execute($member, $target, true);
})->throws(HttpException::class);

test('a verified member has no administrative permissions', function () {
    $member = User::factory()->verified()->create(['is_admin' => false]);

    $this->actingAs($member)
        ->get(route('admin.dashboard'))
        ->assertForbidden();

    $this->actingAs($member)
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('Verify members')
        ->assertDontSee('Admin');
});

test('verification is public while admin status remains private', function () {
    $viewer = User::factory()->create();
    $member = User::factory()->verified()->create(['is_admin' => true]);

    $this->actingAs($viewer)
        ->getJson(route('api.v1.profiles.show', $member))
        ->assertOk()
        ->assertJsonPath('data.is_verified', true)
        ->assertJsonMissingPath('data.is_admin');

    $this->get(route('profiles.show', $member))
        ->assertOk()
        ->assertSee('Verified community member');
});

test('profile updates cannot spoof verification', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->put(route('profiles.update', $member), [
            'name' => $member->name,
            'username' => $member->username,
            'is_verified' => true,
        ])
        ->assertRedirect();

    expect($member->refresh()->is_verified)->toBeFalse();
});

test('an administrator can update verification through the mobile API', function () {
    $administrator = User::factory()->create(['is_admin' => true]);
    $member = User::factory()->create();
    Sanctum::actingAs($administrator, ['mobile']);

    $this->patchJson(route('api.v1.admin.users.verification', $member), ['is_verified' => true])
        ->assertOk()
        ->assertJsonPath('data.is_verified', true);

    expect($member->refresh()->is_verified)->toBeTrue();
});
