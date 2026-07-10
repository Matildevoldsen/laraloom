<?php

namespace Tests\Feature;

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
            'content_url' => 'not-a-url',
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
}
