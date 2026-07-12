<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Notifications\WelcomeToSourcefolk;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash:ascii', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'username' => strtolower($input['username']),
            'email' => $input['email'],
            'onboarding_completed_at' => now(),
            'password' => $input['password'],
        ]);

        $user->notify(new WelcomeToSourcefolk);

        return $user;
    }
}
