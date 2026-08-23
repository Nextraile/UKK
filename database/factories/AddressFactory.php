<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Kost\Models\Address;
use App\Domain\Kost\Models\Kost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Address>
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kost_id' => Kost::factory(),
            'full_address' => fake()->streetAddress(),
            'district' => fake()->randomElement(['Cibeunying', 'Coblong', 'Bandung Wetan', 'Sumur Bandung']),
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => fake()->numerify('40###'),
            'country' => 'Indonesia',
            'latitude' => fake()->latitude(-6.9, -6.8),
            'longitude' => fake()->longitude(107.5, 107.7),
        ];
    }
}
