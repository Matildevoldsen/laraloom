<?php

namespace Database\Factories;

use App\Models\LegalAcceptance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LegalAcceptance>
 */
class LegalAcceptanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'terms_version' => config('legal.terms_version'),
            'privacy_version' => config('legal.privacy_version'),
            'minimum_age' => config('legal.minimum_age'),
            'accepted_at' => now(),
        ];
    }
}
