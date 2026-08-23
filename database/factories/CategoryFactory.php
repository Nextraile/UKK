<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Kost\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Category>
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['Putra', 'Putri', 'Campur']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Kost khusus '.strtolower($name),
        ];
    }
}
