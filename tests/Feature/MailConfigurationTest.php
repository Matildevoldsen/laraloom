<?php

use Illuminate\Mail\Transport\CloudflareTransport;
use Illuminate\Support\Facades\Mail;

test('cloudflare mail uses the first-party API transport', function (): void {
    config()->set('mail.mailers.cloudflare.account_id', 'account-id');
    config()->set('mail.mailers.cloudflare.token', 'email-api-token');

    $mailer = config()->array('mail.mailers.cloudflare');
    $failoverMailers = config()->array('mail.mailers.failover.mailers');
    $transport = Mail::mailer('cloudflare')->getSymfonyTransport();

    expect($mailer)
        ->toMatchArray([
            'transport' => 'cloudflare',
            'account_id' => 'account-id',
            'token' => 'email-api-token',
        ])
        ->and($failoverMailers)->not->toContain('cloudflare')
        ->and($transport)->toBeInstanceOf(CloudflareTransport::class)
        ->and((string) $transport)->toBe('cloudflare');
});

test('octane is configured for frankenphp', function (): void {
    expect(config('octane.server'))->toBe('frankenphp')
        ->and(file_get_contents(base_path('.env.example')))
        ->toContain('MAIL_FROM_ADDRESS="hello@sourcefolk.com"')
        ->toContain('CLOUDFLARE_ACCOUNT_ID=')
        ->toContain('CLOUDFLARE_EMAIL_TOKEN=');
});
