<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use Faker\Factory as Faker;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $pangkatList = [
            'Penata Muda',
            'Penata Muda TK I',
            'Penata',
            'Penata TK I',
            'Pembina',
        ];

        $golonganList = [
            'III/a',
            'III/b',
            'III/c',
            'III/d',
            'IV/a',
        ];

        $jabatanList = [
            'Staf',
            'Analis Kepegawaian',
            'Operator',
            'Pranata Komputer',
            'Kasi',
            'Kaur',
        ];

        for ($i = 1; $i <= 120; $i++) {

            Pegawai::create([
                'nama' => $faker->name(),
                'nip' => '198' . str_pad($i, 16, '0', STR_PAD_LEFT),
                'pangkat' => $faker->randomElement($pangkatList),
                'jabatan' => $faker->randomElement($jabatanList),
                'golongan' => $faker->randomElement($golonganList),
                'status' => $faker->randomElement(['aktif', 'tidak aktif']),
            ]);
        }
    }
}