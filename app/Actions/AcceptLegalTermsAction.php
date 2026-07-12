<?php

namespace App\Actions;

use App\Models\LegalAcceptance;
use App\Models\User;

final class AcceptLegalTermsAction
{
    public function execute(User $user): LegalAcceptance
    {
        return $user->legalAcceptances()->createOrFirst([
            'terms_version' => config()->string('legal.terms_version'),
        ], [
            'privacy_version' => config()->string('legal.privacy_version'),
            'minimum_age' => config()->integer('legal.minimum_age'),
            'accepted_at' => now(),
        ]);
    }
}
