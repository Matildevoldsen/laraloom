<?php

namespace App\Http\Controllers;

use App\Actions\CompleteOnboardingAction;
use App\Http\Requests\CompleteOnboardingRequest;
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

        if ($user->onboarding_completed_at !== null && $user->hasAcceptedCurrentTerms()) {
            return to_route('home');
        }

        return view('legal.acceptance', [
            'effectiveDate' => config()->string('legal.effective_date'),
            'minimumAge' => config()->integer('legal.minimum_age'),
            'requiresUsername' => $user->onboarding_completed_at === null,
            'suggestedUsername' => $user->username,
            'termsVersion' => config()->string('legal.terms_version'),
        ]);
    }

    public function store(
        CompleteOnboardingRequest $request,
        CompleteOnboardingAction $completeOnboarding,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $validated = $request->validated();
        $username = $validated['username'] ?? $user->username;

        abort_unless(is_string($username), 422);

        $completeOnboarding->execute($user, $username);

        return redirect()->intended(route('home'))->with('status', 'Welcome to Sourcefolk.');
    }
}
