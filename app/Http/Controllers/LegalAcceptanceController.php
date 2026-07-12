<?php

namespace App\Http\Controllers;

use App\Actions\AcceptLegalTermsAction;
use App\Http\Requests\AcceptLegalTermsRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LegalAcceptanceController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        if ($user->hasAcceptedCurrentTerms()) {
            return to_route('home');
        }

        return view('legal.acceptance', [
            'effectiveDate' => config()->string('legal.effective_date'),
            'minimumAge' => config()->integer('legal.minimum_age'),
            'termsVersion' => config()->string('legal.terms_version'),
        ]);
    }

    public function store(
        AcceptLegalTermsRequest $request,
        AcceptLegalTermsAction $acceptLegalTerms,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $acceptLegalTerms->execute($user);

        return redirect()->intended(route('home'))->with('status', 'Welcome to Laraloom.');
    }
}
