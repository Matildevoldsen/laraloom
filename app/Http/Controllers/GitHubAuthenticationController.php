<?php

namespace App\Http\Controllers;

use App\Actions\AuthenticateGitHubUserAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class GitHubAuthenticationController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    public function callback(Request $request, AuthenticateGitHubUserAction $authenticate): RedirectResponse
    {
        if ($request->filled('error')) {
            return to_route('login')->withErrors([
                'github' => 'GitHub sign-in was cancelled. No changes were made.',
            ]);
        }

        try {
            $user = $authenticate->execute(Socialite::driver('github')->user());
        } catch (InvalidStateException) {
            return to_route('login')->withErrors([
                'github' => 'Your GitHub sign-in session expired. Please try again.',
            ]);
        } catch (ValidationException $exception) {
            return to_route('login')->withErrors($exception->errors());
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        if ($user->onboarding_completed_at === null || ! $user->hasAcceptedCurrentTerms()) {
            return to_route('legal.acceptance.show');
        }

        return redirect()->intended(route('home'));
    }
}
