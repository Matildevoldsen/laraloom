<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->legalAcceptances()->create([
                'terms_version' => config('legal.terms_version'),
                'privacy_version' => config('legal.privacy_version'),
                'minimum_age' => config('legal.minimum_age'),
                'accepted_at' => now(),
            ]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => Str::lower(fake()->unique()->bothify('maker_????####')),
            'username_changed_at' => null,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'headline' => fake()->sentence(5),
            'bio' => fake()->paragraph(),
            'location' => fake()->city(),
            'website_url' => fake()->url(),
            'github_username' => fake()->userName(),
            'x_username' => fake()->userName(),
            'avatar_url' => null,
            'avatar_disk' => null,
            'avatar_path' => null,
            'stack' => fake()->randomElements(['Laravel', 'Livewire', 'Filament', 'Inertia', 'Vue', 'React'], 3),
            'is_available_for_work' => false,
            'is_admin' => false,
            'is_verified' => false,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function withoutLegalAcceptance(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->legalAcceptances()->delete();
        });
    }

    public function withoutCompletedOnboarding(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_completed_at' => null,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }
}
