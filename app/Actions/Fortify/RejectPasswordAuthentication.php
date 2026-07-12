<?php

namespace App\Actions\Fortify;

use Closure;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Http\Requests\LoginRequest;

final class RejectPasswordAuthentication
{
    /** @throws ValidationException */
    public function handle(LoginRequest $request, Closure $next): never
    {
        throw ValidationException::withMessages([
            'email' => 'Password sign-in is not available. Continue with GitHub.',
        ]);
    }
}
