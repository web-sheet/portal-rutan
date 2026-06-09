<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID'); // Menggunakan lokalisasi Indonesia agar nama data lebih familier
        $categories = ['Elektronik', 'Alat Tulis', 'Perlengkapan Kebersihan', 'Pakaian', 'Medis', 'Makanan & Minuman'];
        
        $data = [];

        for ($i = 1; $i <= 1000; $i++) {
            $data[] = [
                'name' => ucwords($faker->words(3, true)), // Contoh: "Buku Tulis Kotak"
                'category' => $faker->randomElement($categories),
                'stock' => $faker->numberBetween(5, 300),
                'description' => $faker->sentence(10),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Setiap 200 data, langsung insert ke DB untuk menghemat memori RAM
            if ($i % 200 === 0) {
                DB::table('items')->insert($data);
                $data = []; // Kosongkan kembali array untuk batch berikutnya
            }
        }

        // Amankan sisa data jika total looping tidak habis dibagi 200
        if (!empty($data)) {
            DB::table('items')->insert($data);
        }
    }
}