<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RoomType>
 */
class RoomTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<RoomType>
     */
    protected $model = RoomType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Single Bed', 'Double Bed', 'Suite', 'Standard', 'Deluxe', 'VIP']);

        return [
            'kost_id' => Kost::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(3),
            'room_size' => fake()->randomElement(['3x3 m', '3x4 m', '4x4 m', '4x5 m', '5x5 m']),
            'max_occupants' => fake()->numberBetween(1, 4),
            'security_deposit' => fake()->randomElement([500000, 750000, 1000000, 1500000, 2000000]),
            'facilities' => fake()->randomElements([
                'AC', 'Kasur', 'Lemari', 'Meja Belajar', 'Kursi',
                'Kipas Angin', 'Jendela', 'Kamar Mandi Dalam', 'Wastafel', 'Cermin',
            ], fake()->numberBetween(2, 5)),
            'rules' => fake()->randomElements([
                'Dilarang merokok', 'Dilarang membawa hewan', 'Tamu lawan jenis dilarang',
                'Jam malam 22:00', 'Jaga kebersihan',
            ], fake()->numberBetween(2, 4)),
        ];
    }
}
