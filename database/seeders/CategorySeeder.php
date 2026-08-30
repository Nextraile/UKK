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
            // Gender-based categories
            ['name' => 'Putra', 'slug' => 'putra', 'description' => 'Kost khusus laki-laki'],
            ['name' => 'Putri', 'slug' => 'putri', 'description' => 'Kost khusus perempuan'],
            ['name' => 'Campur', 'slug' => 'campur', 'description' => 'Kost untuk laki-laki dan perempuan'],
            
            // Price-based categories
            ['name' => 'Budget', 'slug' => 'budget', 'description' => 'Kost dengan harga terjangkau di bawah Rp 1.5 juta/bulan'],
            ['name' => 'Premium', 'slug' => 'premium', 'description' => 'Kost mewah dengan fasilitas lengkap di atas Rp 3 juta/bulan'],
            
            // Lifestyle categories
            ['name' => 'Syariah', 'slug' => 'syariah', 'description' => 'Kost dengan aturan syariah, terpisah putra dan putri'],
            ['name' => 'Mahasiswa', 'slug' => 'mahasiswa', 'description' => 'Kost prioritas untuk mahasiswa, dekat kampus'],
            ['name' => 'Karyawan', 'slug' => 'karyawan', 'description' => 'Kost untuk pekerja profesional, dekat perkantoran'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
