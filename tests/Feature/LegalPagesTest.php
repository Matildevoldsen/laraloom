<?php

it('keeps the legal documents publicly accessible', function (string $routeName): void {
    $this->get(route($routeName))->assertOk();
})->with([
    'terms' => 'legal.terms',
    'privacy' => 'legal.privacy',
]);

it('states the direct-message and moderation boundaries in the terms', function (): void {
    $this->get(route('legal.terms'))
        ->assertOk()
        ->assertSee('A member may start a direct-message conversation only with someone who follows that member.')
        ->assertSee('they are not end-to-end encrypted')
        ->assertSee('Vask real-time events carry conversation, message, sender, and timestamp identifiers—not decrypted message bodies.')
        ->assertSee('Administrators have no general ability to browse conversations.')
        ->assertSee("Deleting either participant's account removes the live conversation history from the primary database", escape: false)
        ->assertSee('selected messages and related identifiers')
        ->assertSee('known identical copies within 48 hours')
        ->assertSee(route('legal.content-request'), escape: false)
        ->assertSee(route('legal.privacy'), escape: false);
});

it('states the 18 plus rule and separates terms from privacy consent', function (): void {
    $this->get(route('legal.terms'))
        ->assertOk()
        ->assertSee('does not permit accounts for anyone under 18')
        ->assertSee('self-declaration, not verified age assurance')
        ->assertSee('A privacy notice explains data use and is not a request for blanket consent')
        ->assertSee('Version 2026-07-12');

    $this->get(route('legal.privacy'))
        ->assertOk()
        ->assertSee('we do not ask for or store your date of birth')
        ->assertSee('does not use automated processing to make decisions that produce legal or similarly significant effects')
        ->assertSee('does not convert it into consent')
        ->assertSee('Version 2026-07-12');
});

it('explains the actual identity media realtime and California data flows', function (): void {
    $this->get(route('legal.privacy'))
        ->assertOk()
        ->assertSee('user:email')
        ->assertSee('do not request repository or organisation scopes')
        ->assertSee('do not store OAuth access or refresh tokens')
        ->assertSee('private objects in Cloudflare R2')
        ->assertSee('A GitHub-provided avatar remains hosted by GitHub unless you replace it.')
        ->assertSee('it does not receive decrypted message bodies')
        ->assertSee('Administrators cannot ordinarily open or search conversations')
        ->assertSee('does not sell personal information or share it for cross-context behavioural advertising')
        ->assertSee('UK data-protection complaints must be acknowledged within 30 days')
        ->assertSee('generally within one month');
});

it('visibly identifies deployment values that are still missing', function (): void {
    config()->set([
        'legal.operator.name' => null,
        'legal.operator.postal_address' => null,
        'legal.operator.country' => null,
        'legal.operator.legal_email' => null,
        'legal.operator.privacy_email' => null,
        'legal.minimum_age' => null,
        'legal.additional_processors' => null,
        'legal.retention.accounts_and_public_content' => null,
        'legal.retention.direct_messages' => null,
        'legal.retention.security_logs' => null,
        'legal.retention.moderation_and_reports' => null,
        'legal.retention.backups' => null,
    ]);

    $this->get(route('legal.privacy'))
        ->assertOk()
        ->assertSee('Deployment information required')
        ->assertSee('Legal operator name')
        ->assertSee('Direct-message retention schedule')
        ->assertSee('Hosting, database, email, and other processor list')
        ->assertSee('Not configured — required before launch.');
});

it('renders configured operator contact and retention details without a warning', function (): void {
    config()->set([
        'legal.operator.name' => 'Example Community Limited',
        'legal.operator.postal_address' => '1 Example Street, London',
        'legal.operator.country' => 'United Kingdom',
        'legal.operator.legal_email' => 'legal@example.test',
        'legal.operator.privacy_email' => 'privacy@example.test',
        'legal.minimum_age' => '18',
        'legal.additional_processors' => 'Example hosting and transactional email providers.',
        'legal.retention.accounts_and_public_content' => 'For the life of the account, then 30 days.',
        'legal.retention.direct_messages' => 'For the life of the conversation, then 30 days.',
        'legal.retention.security_logs' => '90 days.',
        'legal.retention.moderation_and_reports' => 'One year.',
        'legal.retention.backups' => 'Rotated within 30 days.',
    ]);

    $this->get(route('legal.privacy'))
        ->assertOk()
        ->assertSee('Example Community Limited')
        ->assertSee('privacy@example.test')
        ->assertSee('For the life of the conversation, then 30 days.')
        ->assertSee('Example hosting and transactional email providers.')
        ->assertDontSee('Deployment information required');
});
