<?php

namespace Database\Factories;

use App\Domain\Identity\Models\OtpVerification;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OtpVerification>
 */
class OtpVerificationFactory extends Factory
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
            'otp_code' => hash('sha256', str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT)),
            'expires_at' => now()->addMinutes(15),
            'used_at' => null,
        ];
    }
}
