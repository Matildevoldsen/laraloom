<?php

use App\Actions\DeleteUserAsAdministratorAction;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

test('only strict administrators can delete members', function (): void {
    $member = User::factory()->create();
    $verifiedMember = User::factory()->verified()->create(['is_admin' => false]);
    $target = User::factory()->create();

    $this->actingAs($member)
        ->delete(route('admin.users.destroy', $target), ['confirmation' => $target->username])
        ->assertForbidden();

    $this->actingAs($verifiedMember)
        ->delete(route('admin.users.destroy', $target), ['confirmation' => $target->username])
        ->assertForbidden();

    $this->assertModelExists($target);
});

test('an administrator must type the exact member username', function (): void {
    $administrator = User::factory()->create(['is_admin' => true]);
    $member = User::factory()->create();

    $this->actingAs($administrator)
        ->from(route('admin.users.index'))
        ->delete(route('admin.users.destroy', $member), ['confirmation' => 'wrong-username'])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasErrors('confirmation');

    $this->assertModelExists($member);
});

test('an administrator can permanently delete a member and their private account data', function (): void {
    Storage::fake('r2');

    $administrator = User::factory()->create(['is_admin' => true]);
    $member = User::factory()->create([
        'name' => 'Departing Member',
        'avatar_disk' => 'r2',
        'avatar_path' => 'avatars/departing-member.jpg',
    ]);
    Storage::disk('r2')->put($member->avatar_path, 'avatar');

    $post = Post::factory()->for($member)->create();
    $project = Project::factory()->for($member)->create();
    $token = $member->createToken('mobile')->accessToken;
    DB::table(config()->string('session.table'))->insert([
        'id' => 'departing-member-session',
        'user_id' => $member->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);
    DB::table(config()->string('auth.passwords.users.table', 'password_reset_tokens'))->insert([
        'email' => $member->email,
        'token' => 'unused-reset-token',
        'created_at' => now(),
    ]);

    $conversationId = '11111111-1111-1111-1111-111111111111';
    $messageId = '22222222-2222-2222-2222-222222222222';
    $conversationsTable = config()->string('ai.conversations.tables.conversations', 'agent_conversations');
    $messagesTable = config()->string('ai.conversations.tables.messages', 'agent_conversation_messages');
    DB::table($conversationsTable)->insert([
        'id' => $conversationId,
        'user_id' => $member->id,
        'title' => 'Private AI conversation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table($messagesTable)->insert([
        'id' => $messageId,
        'conversation_id' => $conversationId,
        'user_id' => $member->id,
        'agent' => 'curator',
        'role' => 'user',
        'content' => 'Private content',
        'attachments' => '[]',
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '[]',
        'meta' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($administrator)
        ->delete(route('admin.users.destroy', $member), ['confirmation' => $member->username])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('status', "Departing Member's account was permanently deleted.");

    $this->assertModelMissing($member);
    $this->assertModelMissing($project);
    $this->assertModelMissing($token);
    $this->assertModelExists($post);
    expect($post->refresh()->user_id)->toBeNull()
        ->and(DB::table(config()->string('session.table'))->where('user_id', $member->id)->exists())->toBeFalse()
        ->and(DB::table(config()->string('auth.passwords.users.table', 'password_reset_tokens'))->where('email', $member->email)->exists())->toBeFalse()
        ->and(DB::table($conversationsTable)->where('id', $conversationId)->exists())->toBeFalse()
        ->and(DB::table($messagesTable)->where('id', $messageId)->exists())->toBeFalse();
    Storage::disk('r2')->assertMissing('avatars/departing-member.jpg');
});

test('an administrator cannot delete their own account', function (): void {
    $administrator = User::factory()->create(['is_admin' => true]);
    User::factory()->create(['is_admin' => true]);

    $this->actingAs($administrator)
        ->from(route('admin.users.index'))
        ->delete(route('admin.users.destroy', $administrator), ['confirmation' => $administrator->username])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasErrors(['user' => 'You cannot delete your own administrator account.']);

    $this->assertModelExists($administrator);
});

test('the last remaining administrator cannot be deleted', function (): void {
    $administrator = User::factory()->create(['is_admin' => true]);

    $this->actingAs($administrator)
        ->from(route('admin.users.index'))
        ->delete(route('admin.users.destroy', $administrator), ['confirmation' => $administrator->username])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasErrors(['user' => 'The last remaining administrator cannot be deleted.']);

    $this->assertModelExists($administrator);
});

test('the administrator deletion action independently rejects non administrators', function (): void {
    $member = User::factory()->create();
    $target = User::factory()->create();

    app(DeleteUserAsAdministratorAction::class)->execute($member, $target);
})->throws(HttpException::class);

test('the member management page presents deliberate destructive controls', function (): void {
    $administrator = User::factory()->create(['is_admin' => true]);
    $member = User::factory()->create(['name' => 'Community Member']);

    $this->actingAs($administrator)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Member management')
        ->assertSee('Delete account')
        ->assertSee('Permanently delete Community Member?')
        ->assertSee('Type '.$member->username.' to confirm')
        ->assertSee('This action cannot be reversed.');
});
