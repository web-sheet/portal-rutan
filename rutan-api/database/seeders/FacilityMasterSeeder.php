<?php

namespace Database\Seeders;

use App\Models\FacilityType;
use App\Models\Location;
use Illuminate\Database\Seeder;

class FacilityMasterSeeder extends Seeder
{
    public function run(): void
    {
        // Master Lokasi Rutan
        $locations = [
            'Blok A',
            'Blok B',
            'Blok C',
            'Dapur Utama',
            'Poliklinik',
            'Area Masjid',
            'Gedung Kantor',
            'Area Lapangan',
        ];

        foreach ($locations as $loc) {
            Location::firstOrCreate(['name' => $loc]);
        }

        // Master Jenis Fasilitas
        $facilities = [
            'AC / Pendingin Ruangan',
            'Pintu / Muka Kamar',
            'Kran Air / Pipa',
            'Pompa Air',
            'Lampu / Kelistrikan',
            'Toilet / Closet',
            'Teralis Bersi',
        ];

        foreach ($facilities as $fac) {
            FacilityType::firstOrCreate(['name' => $fac]);
        }
    }
}