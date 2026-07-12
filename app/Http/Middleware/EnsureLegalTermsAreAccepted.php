<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLegalTermsAreAccepted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->onboarding_completed_at !== null && $user->hasAcceptedCurrentTerms()) {
            return $next($request);
        }

        $acceptanceUrl = route('legal.acceptance.show');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $user instanceof User && $user->onboarding_completed_at === null
                    ? 'Complete your profile before using this feature.'
                    : 'You must accept the current Terms of Service before using this feature.',
                'acceptance_url' => $acceptanceUrl,
            ], Response::HTTP_PRECONDITION_REQUIRED);
        }

        if ($request->isMethodSafe()) {
            return redirect()->guest($acceptanceUrl);
        }

        return to_route('legal.acceptance.show');
    }
}
