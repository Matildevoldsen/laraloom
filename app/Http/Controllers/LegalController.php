<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class LegalController extends Controller
{
    public function terms(): View
    {
        return view('legal.terms', $this->documentData());
    }

    public function contentPolicy(): View
    {
        return view('legal.content-policy');
    }

    public function privacy(): View
    {
        return view('legal.privacy', $this->documentData());
    }

    /**
     * @return array{legal: array<string, mixed>, missingLegalDetails: list<string>}
     */
    private function documentData(): array
    {
        $legal = config()->array('legal');

        $requiredDetails = [
            'legal operator name' => data_get($legal, 'operator.name'),
            'operator postal address' => data_get($legal, 'operator.postal_address'),
            'operator country' => data_get($legal, 'operator.country'),
            'legal contact email' => data_get($legal, 'operator.legal_email'),
            'privacy contact email' => data_get($legal, 'operator.privacy_email'),
            'minimum user age' => data_get($legal, 'minimum_age'),
            'account and public-content retention schedule' => data_get($legal, 'retention.accounts_and_public_content'),
            'direct-message retention schedule' => data_get($legal, 'retention.direct_messages'),
            'security-log retention schedule' => data_get($legal, 'retention.security_logs'),
            'moderation and report retention schedule' => data_get($legal, 'retention.moderation_and_reports'),
            'backup deletion schedule' => data_get($legal, 'retention.backups'),
            'hosting, database, email, and other processor list' => data_get($legal, 'additional_processors'),
        ];

        /** @var list<string> $missingLegalDetails */
        $missingLegalDetails = collect($requiredDetails)
            ->filter(static fn (mixed $value): bool => blank($value))
            ->keys()
            ->values()
            ->all();

        return [
            'legal' => $legal,
            'missingLegalDetails' => $missingLegalDetails,
        ];
    }
}
