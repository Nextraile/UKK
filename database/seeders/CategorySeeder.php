<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Kost\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed initial categories for kost classification.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Putra', 'slug' => 'putra', 'description' => 'Kost khusus laki-laki'],
            ['name' => 'Putri', 'slug' => 'putri', 'description' => 'Kost khusus perempuan'],
            ['name' => 'Campur', 'slug' => 'campur', 'description' => 'Kost untuk laki-laki dan perempuan'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
