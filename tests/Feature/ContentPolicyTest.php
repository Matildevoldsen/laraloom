<?php

namespace Tests\Feature;

use App\ContentRequestStatus;
use App\Models\ContentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_policy_discloses_source_and_ai_boundaries(): void
    {
        $this->get(route('legal.content-policy'))
            ->assertOk()
            ->assertSee('We index, summarise, and send people back')
            ->assertSee('raw page content disabled')
            ->assertSee('official API or oEmbed')
            ->assertSee('Opt out, correction, and takedown');
    }

    public function test_rights_holder_can_create_a_durable_content_request(): void
    {
        $this->post(route('legal.content-request.store'), [
            'type' => 'opt_out',
            'content_url' => 'https://publisher.example/article',
            'requester_name' => 'Original Publisher',
            'requester_email' => 'rights@publisher.example',
            'relationship' => 'Publisher and rights holder',
            'details' => 'Please exclude this domain from all automated discovery.',
        ])->assertRedirect(route('legal.content-request'));

        $this->assertDatabaseHas('content_requests', [
            'type' => 'opt_out',
            'content_url' => 'https://publisher.example/article',
            'status' => 'open',
        ]);
    }

    public function test_content_request_requires_verifiable_contact_and_context(): void
    {
        $this->post(route('legal.content-request.store'), [
            'type' => 'remove',
            'content_url' => '',
            'requester_email' => 'not-an-email',
            'details' => 'short',
        ])->assertSessionHasErrors([
            'content_url',
            'requester_name',
            'requester_email',
            'relationship',
            'details',
        ]);

        $this->assertDatabaseCount('content_requests', 0);
    }

    public function test_anyone_can_submit_an_intimate_image_request_without_reuploading_media(): void
    {
        $this->post(route('legal.content-request.store'), [
            'type' => 'intimate_image',
            'content_url' => 'Direct message conversation 42, message 108',
            'requester_name' => 'Image Subject',
            'requester_email' => 'subject@example.com',
            'relationship' => 'Person depicted',
            'details' => 'This intimate depiction was shared without my consent. Please remove it.',
        ])->assertRedirect(route('legal.content-request'))
            ->assertSessionHas('status', fn (string $status): bool => str_contains($status, 'LR-000001'));

        $this->assertDatabaseHas('content_requests', [
            'type' => 'intimate_image',
            'content_url' => 'Direct message conversation 42, message 108',
            'status' => 'open',
        ]);
    }

    public function test_a_privacy_rights_request_does_not_require_a_content_reference(): void
    {
        $response = $this->post(route('legal.content-request.store'), [
            'type' => 'rights',
            'requester_name' => 'Ada Lovelace',
            'requester_email' => 'ada@example.test',
            'relationship' => 'Account holder',
            'details' => 'Please provide a portable copy of the personal information associated with my account.',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('content_requests', [
            'type' => 'rights',
            'content_url' => null,
            'requester_email' => 'ada@example.test',
        ]);
    }

    public function test_only_an_admin_can_progress_and_close_a_rights_request(): void
    {
        $request = ContentRequest::query()->create([
            'type' => 'privacy_complaint',
            'content_url' => 'Account data',
            'requester_name' => 'Privacy Requester',
            'requester_email' => 'privacy@example.com',
            'relationship' => 'Account holder',
            'details' => 'Please investigate how my account information was handled.',
        ]);
        $member = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $payload = [
            'status' => ContentRequestStatus::Resolved->value,
            'resolution_notes' => 'Identity checked and the requested action completed.',
        ];

        $this->actingAs($member)
            ->patch(route('admin.content-requests.status', $request), $payload)
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('admin.content-requests.status', $request), $payload)
            ->assertRedirect();

        expect($request->refresh()->status)->toBe(ContentRequestStatus::Resolved)
            ->and($request->status_updated_by)->toBe($admin->id)
            ->and($request->resolved_at)->not->toBeNull();
    }
}
